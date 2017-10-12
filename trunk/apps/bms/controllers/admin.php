<?php
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadAppClass('SystemController');

class adminController extends SystemController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function indexAction()
	{
		$filter=array(
			'power'=>-1,
		);
		if(isset($_GET['username']))
		{
			$filter['username']=getgpc('username');
			$filter['power']=getgpc('power');
		}
		$this->assign('filter',$filter);
		
		$adminModel=D('Admin');
		$result=$adminModel->getList($filter);
		
		$this->assign('list',$result['list']);
		$this->assign('page_info',$result['page']);
		$this->assign('power',$this->power);
		$this->assign('userid',$this->userId);
		
		$this->assign('title','管理员管理');
		
		$this->display();
	}

	public function addAction()
	{
		if(isPost())
		{
			$data=getPost();
			if($data['password1']!=$data['password'])
				$this->error('两次密码输入不等');
			
			$data['power']=1;
			
			$adminModel=D('Admin');
			$result=$adminModel->add($data);
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('添加管理员成功',pcUrl('admin','index'));
		}
		
		$this->assign('power',$this->power);
		$this->assign('title','添加管理员');
		$this->display('detail');
	}
	
	public function detailAction()
	{
		$userid=getgpc('id');
		if(!$userid)
			$this->error('参数错误');
		
		$adminModel=D('Admin');
		if(isPost())
		{
			$data=getPost();
			
			if($data['password'] && $data['password']!=$data['password1'])
				$this->error('两次密码输入不等');
			
			$result=$adminModel->save($userid,$data);
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('管理员修改成功',pcUrl('admin','index'));
			
			return;
		}
		
		$info=$adminModel->getInfo($userid);
		$this->assign('info',$info);
		$this->assign('power',$this->power);
		
		$this->assign('title','管理员详情');
		$this->display();
	}
	
	public function deleteAction()
	{
		if($this->power!=0)
			$this->error('权限不足');
			
		$admin_id=getgpc('id');
		if(!$admin_id)
			$this->error('参数错误');
			
		$adminModel=D('Admin');
		$flag=$adminModel->where(array(
			'userid'=>$admin_id
		))->delete();
		
		if($flag)
			$this->success('删除成功',pcUrl('admin','index'));
		else
			$this->error('删除失败');
	}
	
}
