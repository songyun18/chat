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
		
		$this->success($user_info);
	}

	public function saveInfoAction()
	{
		$this->checkLogin();
		$user_id=$this->userId;
		
		$this->upload();
		$data=getPost();
		$user_model=D('User');
		$result=$user_model->update($user_id,$data);
		if(!$result['flag'])
			$this->error($result['message']);
		else
			$this->success();
	}
}
