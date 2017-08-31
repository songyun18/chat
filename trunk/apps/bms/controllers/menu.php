<?php
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadAppClass('SystemController');

/**
 * 
 * 后台管理首页
 *
 */
class menuController extends SystemController
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function indexAction()
	{
		$title='dash';
		$this->assign('title',$title);
		
		/*
		//获得文章总数
		$article_count=M('article')->count();
		$this->assign('article_count',$article_count);
		
		//获得用户总数
		$user_count=M('user')->count();
		$this->assign('user_count',$user_count);
		
		//获得用户总数
		$comment_count=M('comment')->count();
		$this->assign('comment_count',$comment_count);

		//获得标签总数
		$tag_count=M('tag')->count();
		$this->assign('tag_count',$tag_count);
		*/
		
		$this->display();
	}
}
