<?php
defined('IN_PHPFRAME') or exit('No permission resources.'); 

Base::loadAppClass('UserController');

class messageController extends UserController
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function indexAction()
	{
		$chat_id=getgpc('chat_id');
		$user2_id=getgpc('user_id');
		if(!$chat_id)
		{
			if(!$user2_id)
				$this->error('参数错误');
			
			$user1_id=$this->userId;
			$where="(user1_id=$user1_id and user2_id=$user2_id) or (user1_id=$user2_id and user2_id=$user1_id)";
			//检查两人是否为好友关系
			$friend_model=D('Friend');
			$friend_model->where($where)->find();
			
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
		}
		
		$filter['chat_id']=$chat_id;
		$model=D('Message');
		$result=$model->getList($filter);
		$result['chat_id']=$chat_id;
		
		$this->success($result);
	}
	
	public function addAction()
	{
		$data=array();
		$data['chat_id']=getgpc('chat_id');
		$data['user_id']=$this->userId;
		$data['message']=getgpc('message');
		
		$model=D('Message');
		$result=$model->add($data);
		if(!$result['flag'])
			$this->error($result['message']);
		else
			$this->success();
	}
}
