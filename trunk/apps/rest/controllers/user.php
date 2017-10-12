<?php
defined('IN_PHPFRAME') or exit('No permission resources.'); 

Base::loadAppClass('RestController');

class userController extends RestController
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function loginAction()
	{
		$user_name=getgpc('user_name');
		$password=getgpc('password');
		if(!$user_name)
			$this->error('请输入用户名');
		if(!$password)
			$this->error('请输入密码');
		
		$model=D('User');
		$result=$model->login($user_name,$password);
		if(!$result['flag'])
			$this->error($result['message']);
		else
			$this->success($result['data']);
	}
	
	public function registerAction()
	{
		$this->sessionInit();
		$data=getPost();
		/*
		if($data['validator']!=$_SESSION['validator'])
			$this->error('验证码错误');
		if($data['password1']!=$data['password'])
			$this->error('两次密码输入不一致');
		*/
		
		$model=D('User');
		$result=$model->add($data);
		if(!$result['flag'])
			$this->error($result['message']);
		else
		{
			$this->sessionStart();
			
			unset($_SESSION['validator']);
			$this->success();
		}
	}
	
	public function logoutAction()
	{
		$model=D('User');
		$model->logout();
		$this->success();
	}

	public function infoAction()
	{
		$this->checkLogin();
		$user_id=$this->userId;
		$model=D('User');
		$temp=$model->getInfo($user_id);
		$user_info=array();
		$user_info['user_id']=$temp['user_id'];
		$user_info['user_name']=$temp['user_name'];
		$user_info['nickname']=$temp['nickname'];
		$user_info['avatar']=$temp['avatar'];
		//检查是否有未读信息
		$mail_model=D('Mail');
		$unread_list=$mail_model->getUnreadMail($user_id);
		if($unread_list)
			$user_info['unread_count']=count($unread_list);
		
		$this->success($user_info);
	}

	public function saveInfoAction()
	{
		$this->checkLogin();
		$user_id=$this->userId;
		
		$this->upload();
		$data=getPost();
		if(!$data['avatar'])
			unset($data['avatar']);
		
		$user_model=D('User');
		$result=$user_model->save($user_id,$data);
		if(!$result['flag'])
			$this->error($result['message']);
		else
			$this->success();
	}

	public function searchAction()
	{
		$this->checkLogin();
		
		$name=getgpc('name');
		if(!$name)
			$this->error('参数错误');

		$user_id=$this->userId;
		
		$user_model=D('User');
		$select="a.user_id,a.user_name,a.nickname,a.avatar,b.friend_id";
		$sql="select $select from #@_user as a 
			left join #@_friend as b on ((b.user1_id=a.user_id and b.user2_id=$user_id) or (b.user2_id=a.user_id and b.user1_id=$user_id))
			where (a.user_name like '%$name%' or a.nickname like '%$name%') and a.user_id!=$user_id";
		$temp=$user_model->query($sql);
		$user_list=array();
		
		$default_avatar=ATTMS_URL.'web/images/avatar.jpg';
		foreach($temp as $key=>$row)
		{
			if(!$row['avatar'])
				$row['avatar']=$default_avatar;
			
			if($row['friend_id'])
				$row['is_friend']=true;
			else
				$row['is_friend']=false;
			
			array_push($user_list,$row);
		}
		
		$this->success($user_list);
	}
}
