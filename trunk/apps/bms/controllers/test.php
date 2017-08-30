<?php 
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadAppClass('SystemController');

class testController extends SystemController
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
		
		$testModel=D('Test');
		$result=$testModel->getList($filter);
		
		$this->assign('list',$result['list']);
		$this->assign('page_info',$result['page']);
		
		$this->assign('title','记录列表');
		$this->display();
	}

	public function addAction()
	{
		if(isPost())
		{
			//$this->upload();
			$data=getPost();
			
			$model=D('Test');
			$result=$model->add($data);
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('添加记录成功',pcUrl('test','index'));
		}

		$this->assign('title','添加记录');
		$this->display('detail');
	}
	
	public function detailAction()
	{
		$test_id=getgpc('id');
		if(!$test_id)
			$this->error('参数错误');
		
		$model=D('Test');
		if(isPost())
		{
			//$this->upload();
			$data=getPost();
			$result=$model->update($test_id,$data);
			
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('修改记录成功',pcUrl('test','index'));
		}
		
		$info=$model->getInfo($test_id);
		$this->assign('info',$info);
		
		$this->assign('title','修改记录');
		$this->display('detail');
	}

	public function deleteAction()
	{
		$test_id=getgpc('id');
		if(!$test_id)
			$this->error('参数错误');
		
		$flag=D('Test')->remove($test_id);
		if(!$flag)
			$this->error('删除失败');
		else
			$this->success('删除成功');
	}
}
