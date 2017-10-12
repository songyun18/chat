<?php 
Base::loadSysClass('Model');

class MessageModel extends Model
{
	public function __construct($table_name='',$table_pre='')
	{
		parent::__construct($table_name,$table_pre);
		
		$fields=array(
			'chat_id'=>array(
				'required'=>true,
				'add'=>true,
				'number'=>true,
				'name'=>'聊天主键',
			),
			'user_id'=>array(
				'required'=>true,
				'add'=>true,
				'number'=>true,
				'name'=>'用户id',
			),
			'message'=>array(
				'required'=>true,
				'strlen'=>array(1,255),
				'checkjs'=>true,
				'name'=>'内容',
			),
			'is_read'=>array(
				'checkjs'=>true,
				'array'=>array(0,1),
				'name'=>'是否阅读',
			),
		);
		$this->setFields($fields);
	}
	
	public function getList($filter=array(),$page_size=10)
	{
		$where=' 1 ';
		
		if(isset($filter['chat_id']))
			$where.=' and a.chat_id ='.$filter['chat_id'];
		
		$count  = $this->getCount($where);    //计算总数
		$page   = new Page($count, $page_size);

		$select="a.*,b.user_name,b.avatar";
		$order = 'message_id desc';
		$limit=$page->firstRow.','.$page->listRows;

		$result=array();
		$sql="select $select from #@_message as a
			left join #@_user as b on b.user_id=a.user_id
			where $where order by $order limit $limit";

		$result['list']=$this->query($sql);
		$default_avatar=ATTMS_URL.'web/images/avatar.jpg';
		
		$unread_ids=array();
		foreach($result['list'] as $key=>$row)
		{
			if(!$row['avatar'])
			{
				$row['avatar']=$default_avatar;
			}
			switch($row['is_read'])
			{
				case 0:
					$row['is_read_exp']='否';
					break;
				case 1:
					$row['is_read_exp']='是';
					break;
			}
			if(isset($filter['user_id']) && $row['user_id']!=$filter['user_id'] && $row['is_read']==0)
				array_push($unread_ids,$row['message_id']);
			
			$result['list'][$key]=$row;
		}
		//更新未读列表
		if($unread_ids)
		{
			$data=array(
				'is_read'=>1
			);
			
			$this->where(array(
				'message_id'=>$unread_ids
			))->update($data);
		}
		
		$result['page']=$page->getPageInfo();
		$result['next_page']=$page->getNextPage();
		return $result;
	}


	public function getCount($where)
	{
		$sql="select count(*) as count from #@_message as a
			left join #@_user as b on b.user_id=a.user_id
			where $where";
		$temp=$this->query($sql);
		return $temp[0]['count'];
	}

	public function getInfo($message_id)
	{
		$select="a.*,b.user_name,b.avatar";
		$sql="select $select from #@_message as a
			left join #@_user as b on b.user_id=a.user_id
			where a.message_id=".$message_id;
		$default_avatar=ATTMS_URL.'web/images/avatar.jpg';
		if($info)
		{
			$info=$info[0];
			
			if(!$info['avatar'])
			{
				$info['avatar']=$default_avatar;
			}
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
		
		$now=time();
		$data1['add_time']=$now;
		$result['flag']=$this->insert($data1);
		if(!$result['flag'])
			$result['message']='插入记录失败';
		//修改chat表的last_message
		$data2=array();
		$data2['last_message']=$data1['message'];
		$chat_model=D('Chat');
		$result=$chat_model->update($data1['chat_id'],$data2);
		
		return $result;
	}

	//修改记录
	public function save($message_id,$data)
	{
		$result=$this->checkPost($data,'save');
		if(!$result['flag'])
			return $result;

		$data1=$result['data'];
		unset($result['data']);
		$result['flag']=false;

		$info=$this->getInfo($message_id);
		if(!$info)
		{
			$result['message_id错误'];
			return $result;
		}

		$result['flag']=$this->where(array(
			'message_id'=>$message_id			
		))->update($data1);

		if(!$result['flag'])
			$result['message']='修改记录失败';

		return $result;
	}

	public function remove($message_id)
	{
		$flag=$this->where(array(
			'message_id'=>$message_id		
		))->delete();

		return $flag;
	}

}
