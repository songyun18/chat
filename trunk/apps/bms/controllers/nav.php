<?php
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadAppClass('SystemController');

class navController extends SystemController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function indexAction()
	{
		$navModel=D('Nav');
		$result=$navModel->getList();
		$this->assign('list',$result['list']);
		
		$this->assign('title','导航管理');
		$this->display();
	}

	public function addAction()
	{
		if(isPost())
		{
			$data=getPost();
			
			$model=D('Nav');
			$result=$model->add($data);
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('添加导航成功',pcUrl('nav','index'));
		}
		
		$this->assign('title','添加导航');
		$this->display('detail');
	}
	
	public function detailAction()
	{
		$nav_id=getgpc('id');
		if(!$nav_id)
			$this->error('参数错误');
		
		$model=D('Nav');
		if(isPost())
		{
			$data=getPost();
			
			$result=$model->update($nav_id,$data);
			
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('添加导航成功',pcUrl('nav','index'));
		}
		
		$info=$model->getInfo($nav_id);
		$this->assign('info',$info);
		
		$this->assign('title','修改导航');
		$this->display('detail');
	}
	
	public function deleteAction()
	{
		$nav_id=getgpc('id');
		if(!$nav_id)
			$this->error('参数错误');
		
		$flag=D('Nav')->remove($nav_id);
		
		if(!$flag)
			$this->error('导航删除失败');
		else
			$this->success('导航删除成功');
	}
}
