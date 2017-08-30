<?php 
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadAppClass('SystemController');

class tagController extends SystemController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function indexAction()
	{
		$filter=array();
		//$filter['title']=getgpc('title');
		$this->assign('filter',$filter);

		$tagModel=D('Tag');
		$result=$tagModel->getList($filter);

		$this->assign('list',$result['list']);
		$this->assign('page_info',$result['page']);

		$this->assign('title','标签列表');
		$this->display();
	}

}
