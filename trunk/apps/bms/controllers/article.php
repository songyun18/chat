<?php
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadAppClass('SystemController');

class articleController extends SystemController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function indexAction()
	{
		$filter=array();
		$filter['title']=getgpc('title');
		$filter['category_id']=getgpc('category_id');
		$this->assign('filter',$filter);
		
		$articleModel=D('Article');
		$result=$articleModel->getList($filter);
		
		$this->assign('list',$result['list']);
		$this->assign('page_info',$result['page']);
		
		//获得文章分类
		$category_list=M('category')->select();
		$this->assign('category_list',$category_list);
		$this->assign('title','文章管理');
		
		$this->display();
	}

	public function addAction()
	{
		$model=D('Article');
		if(isPost())
		{
			$result=$this->upload();
			$data=getPost();
			
			$result=$model->add($data);
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('添加文章成功',pcUrl('article','index'));
		}

		$category_list=D('Category')->select();
		$this->assign('category_list',$category_list);

		$check_js=$model->checkjs();
		$this->assign('check_js',$check_js);
		
		$this->assign('title','添加文章');
		$this->display('detail');
	}
	
	public function detailAction()
	{
		$article_id=getgpc('id');
		if(!$article_id)
			$this->error('参数错误');
		
		$model=D('Article');
		if(isPost())
		{
			$this->upload();
			$data=getPost();
			$result=$model->update($article_id,$data);
			
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('修改文章成功',pcUrl('article','index'));
		}
		
		$info=$model->getInfo($article_id);
		$this->assign('info',$info);
		
		$category_list=D('Category')->select();
		$this->assign('category_list',$category_list);
		$check_js=$model->checkjs();
		$this->assign('check_js',$check_js);
		
		$this->assign('title','修改文章');
		$this->display('detail');
	}

	public function deleteAction()
	{
		$article_id=getgpc('id');
		if(!$article_id)
			$this->error('参数错误');
		
		$flag=D('Article')->remove($article_id);
		if(!$flag)
			$this->error('删除失败');
		else
			$this->success('删除成功');
	}

	public function batchDeleteAction()
	{
		$result=array(
			'flag'=>false,
			'message'=>'',
		);
		
		$ids=getgpc('ids');
		$result['flag']=D('Article')->remove($ids);
		
		if(!$result['flag'])
		{
			$result['message']='删除失败';
		}

		$result=json_encode($result);
		echo $result;
	}
}
