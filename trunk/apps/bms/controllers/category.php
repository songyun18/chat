<?php
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadAppClass('SystemController');

class categoryController extends SystemController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function indexAction()
	{
		$categoryModel=D('Category');
		$list=$categoryModel->select();
		$this->assign('list',$list);
		
		$this->assign('title','分类管理');
		$this->display();
	}

	public function addAction()
	{
		if(isPost())
		{
			$data=getPost();
			
			$model=D('Category');
			$result=$model->add($data);
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('添加分类成功',pcUrl('category','index'));
		}
		
		$this->assign('title','添加分类');
		$this->display('detail');
	}
	
	public function detailAction()
	{
		$category_id=getgpc('id');
		if(!$category_id)
			$this->error('参数错误');
		
		$model=D('Category');
		if(isPost())
		{
			$data=getPost();
			
			$result=$model->update($category_id,$data);
			
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('添加分类成功',pcUrl('category','index'));
		}
		
		$info=$model->getInfo($category_id);
		$this->assign('info',$info);
		
		$this->assign('title','修改分类');
		$this->display('detail');
	}
	
	public function deleteAction()
	{
		$category_id=getgpc('id');
		if(!$category_id)
			$this->error('参数错误');
		
		$flag=D('Category')->remove($category_id);
		
		if(!$flag)
			$this->error('分类删除失败');
		else
			$this->success('分类删除成功');
	}
}
