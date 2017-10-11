<?php 
Base::loadSysClass('Model');

class MailModel extends Model
{
	public function __construct($table_name='',$table_pre='')
	{
		parent::__construct($table_name,$table_pre);
		
		$fields=array(
			'user_id'=>array(
				'required'=>true,
				'number'=>true,
				'name'=>'接收人id',
			),
			'send_id'=>array(
				'required'=>true,
				'number'=>true,
				'name'=>'发送人id',
			),
			'send_content'=>array(
				'required'=>true,
				'strlen'=>array(1,255),
				'name'=>'发信人内容',
			),
			'recive_content'=>array(
				'required'=>true,
				'strlen'=>array(1,255),
				'name'=>'收信人内容',
			),
			'type'=>array(
				'required'=>true,
				'array'=>array(0,1,2),
				'name'=>'站内信类型',
			),
			'status'=>array(
				'array'=>array(0,1,2),
				'name'=>'状态',
			),
		);
		$this->setFields($fields);
	}

	public function getList($filter=array(),$page_size=10)
	{
		$where=' 1 ';

		if(isset($filter['user_id']))
			$where.=' and (a.user_id='.$filter['user_id'].' or a.send_id='.$filter['user_id'].')';
		if(isset($filter['status']) && $filter['status']!=-1)
			$where.=' and a.status='.$filter['status'];

		$count  = $this->getCount($where);    //计算总数
		$page   = new Page($count, $page_size);

		$select="a.*,b.avatar,b.nickname as user_name";
		$order = 'mail_id desc';
		$limit=$page->firstRow.','.$page->listRows;

		$result=array();
		$sql="select $select from #@_mail as a
			left join #@_user as b on b.user_id=a.send_id
			where $where order by $order limit $limit";

		$result['list']=$this->query($sql);
		$default_avatar=ATTMS_URL.'web/images/avatar.jpg';
		foreach($result['list'] as $key=>$row)
		{
			if(!$row['avatar'])
			{
				$row['avatar']=$default_avatar;
			}
			switch($row['status'])
			{
				case 0:
					$row['status_exp']='未读';
					break;
				case 1:
					$row['status_exp']='已确定';
					break;
				case 2:
					$row['status_exp']='已拒绝';
					break;
			}
			
			if(isset($filter['user_id']) && $row['send_id']==$filter['user_id'])
				$row['is_sender']=true;
			
			$row['add_time_exp']=date('Y-m-d H:i:s',$row['add_time']);
			if($row['update_time'])
				$row['update_time_exp']=date('Y-m-d H:i:s',$row['update_time']);
			
			$result['list'][$key]=$row;
		}
		
		$result['page']=$page->getPageInfo();
		$result['next_page']=$page->getNextPage();
		return $result;
	}
	
	public function getCount($where)
	{
		$sql="select count(*) as count from #@_mail as a
			left join #@_user as b on b.user_id=a.send_id
			where $where";
		$temp=$this->query($sql);
		return $temp[0]['count'];
	}
	
	public function getInfo($mail_id)
	{
		$select="a.*,b.avatar,b.nickname as user_name";
		$sql="select $select from #@_mail as a
			left join #@_user as b on b.user_id=a.send_id
			where a.mail_id=".$mail_id;
		$info=$this->query($sql);
		$default_avatar=ATTMS_URL.'web/images/avatar.jpg';
		if($info)
		{
			$info=$info[0];
			
			if(!$info['avatar'])
			{
				$info['avatar']=$default_avatar;
			}
			switch($info['status'])
			{
				case 0:
					$info['status_exp']='未读';
					break;
				case 1:
					$info['status_exp']='已确定';
					break;
				case 2:
					$info['status_exp']='已拒绝';
					break;
			}
			$info['add_time_exp']=date('Y-m-d H:i:s',$info['add_time']);
			if($info['update_time'])
				$info['update_time_exp']=date('Y-m-d H:i:s',$info['update_time']);
		}
		
		return $info;
	}
	
	//添加记录
	public function add($data)
	{
		$result=$this->checkPost($data);
		if(!$result['flag'])
			return $result;
		
		$data1=$result['data'];
		unset($result['data']);
		$result['flag']=false;
		
		$now=time();
		$data1['status']=0;
		//检查是否有同类信息，拒绝重复发送
		$flag=$this->where($data1)->count();
		if($flag)
		{
			$result['message']='请不要重复发送信息';
			return $result;
		}
		
		$data1['add_time']=$now;
		$data1['update_time']=0;
		
		$result['flag']=parent::add($data1);
		if(!$result['flag'])
			$result['message']='插入记录失败';
		
		return $result;
	}
	
	//修改记录
	public function update($mail_id,$data)
	{
		$result=$this->checkPost($data,'update');
		if(!$result['flag'])
			return $result;

		$data1=$result['data'];
		unset($result['data']);
		$result['flag']=false;

		$info=$this->getInfo($mail_id);
		if(!$info)
		{
			$result['message']='mail_id错误';
			return $result;
		}
		
		$data1['update_time']=time();
		$result['flag']=$this->where(array(
			'mail_id'=>$mail_id			
		))->save($data1);
		
		if(!$result['flag'])
			$result['message']='修改记录失败';

		return $result;
	}

	public function confirm($mail_id,$status)
	{
		$result=array(
			'flag'=>false,
			'message'=>'',
		);
		$info=$this->getInfo($mail_id);
		if(!$info)
		{
			$result['message']='mail_id不存在';
			return $result;
		}
		if($status==0)
		{
			$result['message']='status状态错误';
			return $result;
		}
		if($info['type']==0 && $status==2)
		{
			$result['message']='status状态错误';
			return $result;
		}
		$this->execute('START TRANSACTION');
		$flags=array();
		
		//处理成功代码
		if($status==1)
		{
			//处理好友请求
			if($info['type']==1)
			{
				$data=array();
				$data['user1_id']=$info['send_id'];
				$data['user2_id']=$info['user_id'];
				$friends_model=D('Friend');
				$result1=$friends_model->add($data);
				if(!$result1['flag'] && !$result['message'])
				{
					array_push($flags,$result1['flag']);
					$result['message']=$result1['message'];
				}
			}
		}

		//保存状态变化
		$data=array();
		$data['status']=$status;
		$result1=$this->update($mail_id,$data);
		if(!$result1['flag'] && !$result['message'])
		{
			array_push($flags,$result1['flag']);
			$result['message']=$result1['message'];
		}
		
		$result['flag']=true;
		foreach($flags as $flag)
		{
			if(!$flag)
			{
				$result['flag']=false;
				break;
			}
		}
		if($result['flag'])
			$this->execute('COMMIT');
		else
			$this->execute('ROLLBACK');
		
		return $result;
	}
	
	public function remove($mail_id)
	{
		$flag=$this->where(array(
			'mail_id'=>$mail_id		
		))->delete();
		
		return $flag;
	}
	
	public function getUnreadMail($user_id)
	{
		$filter=array();
		$filter['user_id']=$user_id;
		$filter['status']=0;
		
		$temp=$this->getList($filter);
		$result=array();
		
		foreach($temp['list'] as $key=>$row)
		{
			if($row['is_sender'])
				continue;
			array_push($result,$row);
		}
		
		return $list;
	}
}
