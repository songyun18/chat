<?php
defined('IN_PHPFRAME') or exit('No permission resources.'); 

Base::loadAppClass('CommonController');

class indexController extends CommonController
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function indexAction()
	{
		$filter=array();
		$filter['tag']=getgpc('tag');
		$filter['title']=getgpc('keywords');
		
		$article_model=D('Article');
		//获取文章列表
		$result=$article_model->getList($filter);
		$this->assign('article_list',$result['list']);
		$this->assign('page_info',$result['page']);
		$this->assign('filter',$filter);
		
		$this->_getHotList();
		$this->_getHotTags();
		$this->display();
	}
	
	//获取博客详情
	public function articleAction()
	{
		$article_id=getgpc('id');
		if(!$article_id)
			$this->error('参数错误');
		
		$article_model=D('Article');
		//获取文章列表
		$article_info=$article_model->getInfo($article_id);
		$this->assign('info',$article_info);
		
		//获取上一篇和下一篇
		$prev_next=$article_model->getPrevNext($article_id);
		$this->assign('prev_next',$prev_next);
		$this->_getHotList();
		
		$this->assign('user_id',$this->userId);
		$this->assign('article_id',$article_id);
		
		$this->display();
	}

	public function navAction()
	{
		$nav_id=getgpc('id');
		if(!$nav_id)
			$this->error('参数错误');
		
		$info=D('Nav')->getInfo($nav_id);
		$this->assign('info',$info);
		
		$this->_getHotList();
		
		$this->display();
	}
	
	private function _getHotList()
	{
		$article_model=D('Article');
		
		//获取热门文章
		$hot_list=$article_model->getHotList();
		$this->assign('hot_list',$hot_list);
	}

	private function _getHotTags()
	{
		$tag_model=D('Tag');
		$tag_list=$tag_model->getHotTags();
		$this->assign('tag_list',$tag_list);
	}
	
	public function commentListAction()
	{
		$filter=array();
		$filter['article_id']=getgpc('article_id');
		
		$model=D('Comment');
		$result=$model->getList($filter,5);
		$result=json_encode($result);
		echo $result;
	}

	public function commentAddAction()
	{
		$result=array(
			'flag'=>false,
			'message'=>'',
		);
		if(!$this->userId)
		{
			$result['message']='用户未登录';
			$result=json_encode($result);
			echo $result;
			die();
		}
		
		$content=getgpc('content');
		$article_id=getgpc('article_id');
		$parent_id=getgpc('parent_id');
		if(!$parent_id)
			$parent_id=0;
		if(!$article_id)
		{
			$result['message']='article_id为必填';
			$result=json_encode($result);
			echo $result;
			die();
		}
		
		if(!$content)
		{
			$result['message']='用户未登录';
			$result=json_encode($result);
			echo $result;
			die();
		}

		$data=array();
		$data['content']=$content;
		$data['user_id']=$this->userId;
		$data['parent_id']=$parent_id;
		$data['article_id']=$article_id;
		$data['ip']=ip();
		
		$model=D('Comment');
		$result=$model->add($data);
		
		$result=json_encode($result);
		echo $result;
	}

	public function validatorAction()
	{
		$this->sessionStart();
		
		$checkcode = Base::loadSysClass('Checkcode');
		$checkcode->background='#FFFFFF';
		//$checkcode->font_color='#ffd701';
		$checkcode->doimage();
		$_SESSION['validator'] = $checkcode->get_code();
	}
}
