<?php
defined('IN_PHPFRAME') or exit('No permission resources.'); 

Base::loadAppClass('UserController');

class friendController extends UserController
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function indexAction()
	{
		$filter=array();
		$filter['user_id']=$this->userId;
		$user_id=$this->userId;
		$model=D('Friend');
		$result=$model->getList($filter);
		$list=$result['list'];
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
	
	//发送好友请求
	public function addAction()
	{
		$user_id=getgpc('user_id');
		if(!$user_id)
			$this->error('参数错误');
		
		$send_id=$this->userId;
		//检查两人是否为好友
		$friend_model=D('Friend');
		$is_friend=$friend_model->isFriends($send_id,$user_id);
		if($is_friend)
			$this->error('你们已经是好友');
		
		//发送站内信
		//获取用户昵称
		$user_model=D('User');
		$send_info=$user_model->getInfo($send_id);
		$user_info=$user_model->getInfo($user_id);
		
		$data=array();
		$data['user_id']=$user_id;
		$data['send_id']=$send_id;
		$data['send_content']='你希望和'.$user_info['nickname'].'成为好友';
		$data['recive_content']=$send_info['nickname'].'希望和你成为好友';
		$data['type']=1;
		$mail_model=D('Mail');
		$result=$mail_model->add($data);
		if(!$result['flag'])
			$this->error($result['message']);
		else
			$this->success();
		
	}
}
