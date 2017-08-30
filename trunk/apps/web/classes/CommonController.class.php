<?php
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadSysClass('Controller');

class CommonController extends Controller
{
	protected $userId=0;
	protected $userName='';
	
	public function __construct()
	{
		//$this->_init();
		$this->sessionInit();
		
		if (!empty($_SESSION['user_id']))
		{
			$this->userId=$_SESSION['user_id'];
			$this->userName=$_SESSION['user_name'];
		}
		
		$this->layout='common';
		
		//获取网站名和网站地址
		$result=D('Config')->getCacheValues(array(
			'site_name',
			'site_url',
			'icp_number',
		));
		$this->assign('site_name',$result['site_name']);
		$this->assign('site_url',$result['site_url']);
		$this->assign('icp_number',$result['icp_number']);
		//获取网站导航
		$cache=D('Cache');
		$key='site_nav';
		$site_nav=$cache->execute($key,function()
		{
			return D('Nav')->getNavs();
		});
		$this->assign($key,$site_nav);

		//获取尾部的友情链接
		$key='friend_link';
		$friend_link=$cache->execute($key,function()
		{
			return D('Link')->getLinks();
		});
		$this->assign('friend_link',$friend_link);

		//获取当前的nav状态
		$nav_now=-1;
		if(ROUTE_C=='index' && ROUTE_A=='index')
		{
			$nav_now='0';
		}
		if(ROUTE_C=='index' && ROUTE_A=='nav')
		{
			$nav_now=getgpc('id');
		}
		$this->assign('nav_now',$nav_now);
		
		$this->assign('user_id',$this->userId);
		$this->assign('user_name',$this->userName);
	}

	public function checkLogin()
	{
		if(!$this->userId)
			$this->error('用户未登录');
	}
}
