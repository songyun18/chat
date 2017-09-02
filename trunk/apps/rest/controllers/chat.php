<?php
defined('IN_PHPFRAME') or exit('No permission resources.'); 

Base::loadAppClass('UserController');

class chatController extends UserController
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function indexAction()
	{
		$filter=array();
		$filter['user_id']=$this->userId;
		$model=D('Chat');
		$result=$model->getList($filter);
		$list=$result['list'];
		
		$user_id=$this->userId;
		foreach($list as $key=>$row)
		{
			if($row['user1_id']==$user_id)
			{
				$row['user_id']=$row['user2_id'];
				$row['user_name']=$row['user2_name'];
				$row['avatar']=$row['user2_avatar'];
			}
			else
			{
				$row['user_id']=$row['user1_id'];
				$row['user_name']=$row['user1_name'];
				$row['avatar']=$row['user1_avatar'];
			}
			
			unset($row['user1_id']);
			unset($row['user1_name']);
			unset($row['user1_avatar']);
			
			unset($row['user2_id']);
			unset($row['user2_name']);
			unset($row['user2_avatar']);
			
			$list[$key]=$row;
		}
		
		$this->success($list);
	}

	public function checkAction()
	{
		$user2_id=getgpc('user_id');
		$user1_id=$this->userId;
		
		$where="(user1_id=$user1_id and user2_id=$user2_id) or (user1_id=$user2_id and user2_id=$user1_id)";
		//检查两人是否为好友关系
		$friend_model=D('Friend');
		$flag=$friend_model->where($where)->count();
		if(!$flag)
			$this->error('非好友不能聊天');
		
		//根据用户id来获取chat_id
		$chat_model=D('Chat');
		$info=$chat_model->where($where)->find();
		//不存在则插入数据
		if(!$info)
		{
			$data=array();
			$data['user1_id']=$user1_id;
			$data['user2_id']=$user2_id;
			$result=$chat_model->add($data);
			if(!$result['flag'])
				$this->error('插入聊天列表失败');
			
			$chat_id=$chat_model->getInsertId();
		}
		else
			$chat_id=$info['chat_id'];
		
		$this->success($chat_id);
	}
}
