<?php
defined('IN_PHPFRAME') or exit('No permission resources.'); 
Base::loadAppClass('RestController');

class indexController extends RestController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function layoutAction()
	{
		$result=array();
		//获取网站名和网站地址
		$result=D('Config')->getCacheValues(array(
			'site_name',
			'site_url',
			'icp_number',
		));
		
		//获取网站导航
		$cache=D('Cache');
		$key='site_nav';
		$site_nav=$cache->execute($key,function()
		{
			return D('Nav')->getNavs();
		});
		$result['site_nav']=$site_nav;
		
		//获取尾部的友情链接
		$key='friend_link';
		$friend_link=$cache->execute($key,function()
		{
			return D('Link')->getLinks();
		});
		$result['friend_link']=$friend_link;
		$result['user_id']=$this->userId;
		$result['user_name']=$this->userName;
		$this->success($result);
	}
	
	public function indexAction()
	{
		$result=array();
		
		$filter=array();
		$filter['tag']=getgpc('tag');
		$filter['title']=getgpc('title');
		
		$article_model=D('Article');
		//获取文章列表
		$result=$article_model->getList($filter);
		$result['hot_list']=$this->_getHotList();
		$result['tag_list']=$this->_getHotTags();
		
		$this->success($result);
	}

	public function articleAction()
	{
		$result=array();
		
		$article_id=getgpc('article_id');
		if(!$article_id)
			$this->error('参数错误');
		
		$article_model=D('Article');
		//获取文章列表
		$article_info=$article_model->getInfo($article_id);
		$result['info']=$article_info;
		
		//获取上一篇和下一篇
		$prev_next=$article_model->getPrevNext($article_id);
		$result['prev_next']=$prev_next;
		$result['hot_list']=$this->_getHotList();
		$result['artilce_id']=$article_id;
		
		$this->success($result);
	}
	
	public function commentListAction()
	{
		$filter=array();
		$filter['article_id']=getgpc('article_id');
		
		$model=D('Comment');
		$result=$model->getList($filter,5);
		$this->success($result);
	}

	public function commentAddAction()
	{
		$result=array(
			'flag'=>false,
			'message'=>'',
		);
		if(!$this->userId)
			$this->error('用户未登录');
		
		$content=getgpc('content');
		$article_id=getgpc('article_id');
		$parent_id=getgpc('parent_id');
		if(!$parent_id)
			$parent_id=0;
		if(!$article_id)
			$this->error('article_id为必填');
		
		if(!$content)
			$this->error('用户未登录');

		$data=array();
		$data['content']=$content;
		$data['user_id']=$this->userId;
		$data['parent_id']=$parent_id;
		$data['article_id']=$article_id;
		$data['ip']=ip();
		
		$model=D('Comment');
		$result=$model->add($data);
		$this->success($result);
	}

	public function navAction()
	{
		$nav_id=getgpc('id');
		if(!$nav_id)
			$this->error('参数错误');
		
		$result['info']=D('Nav')->getInfo($nav_id);
		$result['hot_list']=$this->_getHotList();
		
		$this->success($result);
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
	
	private function _getHotList()
	{
		$article_model=D('Article');
		
		//获取热门文章
		$hot_list=$article_model->getHotList();
		return $hot_list;
	}
	
	private function _getHotTags()
	{
		$tag_model=D('Tag');
		$tag_list=$tag_model->getHotTags();
		return $tag_list;
	}

}
