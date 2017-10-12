<?php 
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadAppClass('SystemController');

class friendController extends SystemController
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
		
		$friendModel=D('Friend');
		$result=$friendModel->getList($filter);
		
		$this->assign('list',$result['list']);
		$this->assign('page_info',$result['page']);
		
		$this->assign('title','好友列表');
		$this->display();
	}

	public function addAction()
	{
		$model=D('Friend');
		if(isPost())
		{
			//$this->upload();
			$data=getPost();
			
			$result=$model->add($data);
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('添加好友成功',pcUrl('friend','index'));
		}

		$check_js=$model->checkjs();
		$this->assign('check_js',$check_js);
		
		$this->assign('title','添加好友');
		$this->display('detail');
	}
	
	public function detailAction()
	{
		$friend_id=getgpc('id');
		if(!$friend_id)
			$this->error('参数错误');
		
		$model=D('Friend');
		if(isPost())
		{
			//$this->upload();
			$data=getPost();
			$result=$model->save($friend_id,$data);
			
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('修改好友成功',pcUrl('friend','index'));
		}
		
		$info=$model->getInfo($friend_id);
		$this->assign('info',$info);
		
		$check_js=$model->checkjs();
		$this->assign('check_js',$check_js);
		
		$this->assign('title','修改好友');
		$this->display('detail');
	}

	public function deleteAction()
	{
		$friend_id=getgpc('id');
		if(!$friend_id)
			$this->error('参数错误');
		
		$flag=D('Friend')->remove($friend_id);
		if(!$flag)
			$this->error('删除失败');
		else
			$this->success('删除成功');
	}
}
