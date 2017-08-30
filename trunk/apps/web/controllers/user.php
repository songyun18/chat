<?php
defined('IN_PHPFRAME') or exit('No permission resources.'); 

Base::loadAppClass('CommonController');

class userController extends CommonController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function loginAction()
	{
		if(isPost())
		{
			$user_name=getgpc('user_name');
			$password=getgpc('password');
			$back=getgpc('back');
			
			if(!$user_name)
				$this->error('请输入用户名');
			if(!$password)
				$this->error('请输入密码');
			
			$model=D('User');
			$result=$model->login($user_name,$password);
			if(!$result['flag'])
				$this->error($result['message']);
			else
				//$this->success('登录成功',pcUrl('index','index'));
				//$this->jump(pcUrl('index','index'));
				$this->jump($back);
		}
		
		$back=$_SERVER['HTTP_REFERER'];
		$this->assign('back',$back);
		$this->display();
	}
	
	public function registerAction()
	{
		if(isPost())
		{
			$this->sessionInit();
			
			$data=getPost();
			if($data['validator']!=$_SESSION['validator'])
				$this->error('验证码错误');
			
			if($data['password1']!=$data['password'])
				$this->error('两次密码输入不一致');
			
			$model=D('User');
			$result=$model->add($data);
			if(!$result['flag'])
				$this->error($result['message']);
			else
			{
				$this->sessionStart();
				
				unset($_SESSION['validator']);
				$this->success('注册成功',pcUrl());
			}
		}
		$this->display();
	}

	public function logoutAction()
	{
		$model=D('User');
		$model->logout();
		
		$this->jump($_SERVER['HTTP_REFERER']);
	}
}
