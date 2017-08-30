<?php
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadAppClass('SystemController');

class linkController extends SystemController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function indexAction()
	{
		$linkModel=D('Link');
		$result=$linkModel->getList();
		$this->assign('list',$result['list']);
		
		$this->assign('title','友情链接管理');
		$this->display();
	}

	public function addAction()
	{
		if(isPost())
		{
			$data=getPost();
			
			$model=D('Link');
			$result=$model->add($data);
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('添加友情链接成功',pcUrl('link','index'));
		}
		
		$this->assign('title','添加友情链接');
		$this->display('detail');
	}
	
	public function detailAction()
	{
		$link_id=getgpc('id');
		if(!$link_id)
			$this->error('参数错误');
		
		$model=D('Link');
		if(isPost())
		{
			$data=getPost();
			
			$result=$model->update($link_id,$data);
			
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('添加友情链接成功',pcUrl('link','index'));
		}
		
		$info=$model->getInfo($link_id);
		$this->assign('info',$info);
		
		$this->assign('title','修改友情链接');
		$this->display('detail');
	}
	
	public function deleteAction()
	{
		$link_id=getgpc('id');
		if(!$link_id)
			$this->error('参数错误');
		
		$flag=D('Link')->remove($link_id);
		
		if(!$flag)
			$this->error('友情链接删除失败');
		else
			$this->success('友情链接删除成功');
	}
}
