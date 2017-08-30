<?php
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadAppClass('SystemController');

class dataController extends SystemController
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function indexAction()
	{
		$filter=array();
		$filter['user_id']=getgpc('user_id');
		$filter['user_name']=getgpc('user_name');
		$filter['bank_name']=getgpc('bank_name');
		$filter['type']=getgpc('type');
		$filter['money']=getgpc('money');
		$filter['desc']=getgpc('desc');
		
		$bank_model=D('Bank');
		$bank_list=$bank_model->getList($filter);
		$model=D('Data');
		
		$result=$model->getList($filter);
		
		$this->assign('filter',$filter);
		$this->assign('bank_list',$bank_list);
		$this->assign('list',$result['list']);
		$this->assign('page_info',$result['page']);			
		$title='数据管理';
		$this->assign('title',$title);
		
		$this->display();
	}

	public function detailAction()
	{
		$id=getgpc('id');
		if(!$id)
			$this->error('参数错误');
		
		$model=D('Data');
		$info=$model->getInfo($id);
		
		$title='数据详情';
		$this->assign('title',$title);
		
		$this->assign('info',$info);
		$this->display();
	}

	public function deleteAction()
	{
		$id=getgpc('id');
		if(!$id)
			$this->error('参数错误');
		
		$model=D('Data');
		$result=$model->remove($id);
		if(!$result['flag'])
			$this->error($result['message']);
		else
			$this->success('删除数据成功');
	}
}
