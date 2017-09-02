<?php 
Base::loadSysClass('Model');

class ChatModel extends Model
{
	public function __construct($table_name='',$table_pre='')
	{
		parent::__construct($table_name,$table_pre);

		$fields=array(
			'user1_id'=>array(
				'required'=>true,
				'add'=>true,
				'checkjs'=>true,
				'number'=>true,
				'name'=>'用户1id',
			),
			'user2_id'=>array(
				'required'=>true,
				'add'=>true,
				'checkjs'=>true,
				'number'=>true,
				'name'=>'用户2id',
			),
			'last_message'=>array(
				'checkjs'=>true,
				'strlen'=>array(1,255),
				'name'=>'最新信息',
			),
		);
		$this->setFields($fields);
	}

	public function getList($filter=array(),$page_size=10)
	{
		$where=' 1 ';

		/*
		if(isset($filter['title']))
			$where.=' and a.title like "%'.$filter['title'].'%"';
		 */

		$count  = $this->getCount($where);    //计算总数
		$page   = new Page($count, $page_size);

		$select="a.*,b.nickname as user1_name,b.avatar as user1_avatar,c.nickname as user2_name,c.avatar as user2_avatar";
		$order = 'a.chat_id desc';
		$limit=$page->firstRow.','.$page->listRows;

		$result=array();
		$sql="select $select from #@_chat as a
			left join #@_user as b on b.user_id=a.user1_id
			left join #@_user as c on c.user_id=a.user2_id
			where $where order by $order limit $limit";
		$result['list']=$this->query($sql);
		
		foreach($result['list'] as $key=>$row)
		{
			$row['add_time_exp']=date('Y-m-d H:i:s',$row['add_time']);
			$row['update_time_exp']=date('Y-m-d',$row['update_time']);
			
			$default_avatar=ATTMS_URL.'web/images/avatar.jpg';
			if(!$row['user1_avatar'])
			{
				$row['user1_avatar']=$default_avatar;
			}
			if(!$row['user2_avatar'])
			{
				$row['user2_avatar']=$default_avatar;
			}
			
			$result['list'][$key]=$row;
		}
		
		//$result['page']=$page->getPageInfo();
		$result['next_page']=$page->getNextPage();
		return $result;
	}


	public function getCount($where)
	{
		$sql="select count(*) as count from #@_chat as a
			left join #@_user as b on b.user_id=a.user1_id
			left join #@_user as c on c.user_id=a.user2_id
			where $where";
		$temp=$this->query($sql);
		return $temp[0]['count'];
	}

	public function getInfo($chat_id)
	{
		$select="a.*,b.nickname as user1_name,b.avatar as user1_avatar,c.nickname as user2_name,c.avatar as user2_avatar";
		
		$sql="select $select from #@_chat as a
			left join #@_user as b on b.user_id=a.user1_id
			left join #@_user as c on c.user_id=a.user2_id
			where a.chat_id=".$chat_id;
		$info=$this->query($sql);
		if($info)
		{
			$info=$info[0];
			$info['add_time_exp']=date('Y-m-d H:i:s',$info['add_time']);
			
			$default_avatar=ATTMS_URL.'web/images/avatar.jpg';
			if(!$info['user1_avatar'])
			{
				$info['user1_avatar']=$default_avatar;
			}
			if(!$info['user2_avatar'])
			{
				$info['user2_avatar']=$default_avatar;
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
		$data1['add_time']=time();
		$result['flag']=parent::add($data1);
		if(!$result['flag'])
			$result['message']='插入记录失败';

		return $result;
	}

	//修改记录
	public function update($chat_id,$data)
	{
		$result=$this->checkPost($data,'update');
		if(!$result['flag'])
			return $result;

		$data1=$result['data'];
		unset($result['data']);
		$result['flag']=false;

		$info=$this->getInfo($chat_id);
		if(!$info)
		{
			$result['chat_id错误'];
			return $result;
		}

		$data1['update_time']=time();
		$result['flag']=$this->where(array(
			'chat_id'=>$chat_id			
		))->save($data1);

		if(!$result['flag'])
			$result['message']='修改记录失败';

		return $result;
	}

	public function remove($chat_id)
	{
		$flag=$this->where(array(
			'chat_id'=>$chat_id		
		))->delete();
		if(!$flag)
			return $flag;
		//删除聊天内容
		$message_model=D('Message');
		$flag=$message_model->where(array(
			'chat_id'=>$chat_id
		))->delete();

		return $flag;
	}

}
