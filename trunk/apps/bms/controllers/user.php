<?php
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadAppClass('SystemController');

class userController extends SystemController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function indexAction()
	{
		$filter=array();
		$filter['user_name']=getgpc('user_name');

		$model=D('User');
		$result=$model->getList($filter);

		$this->assign('filter',$filter);
		$this->assign('list',$result['list']);
		$this->assign('page_info',$result['page']);

		$title='用户管理';
		$this->assign('title',$title);

		$this->display();
	}

	public function addAction()
	{
		if(isPost())
		{
			$data=getPost();

			if(!$data['password1'])
				$this->error('请确认密码');
			if($data['password']!=$data['password'])
				$this->error('两次密码输入不一致');

			$model=D('User');
			$result=$model->add($data);

			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('用户创建成功',pcUrl('user','index'));
		}
		$title='添加用户';
		$this->assign('title',$title);
		$this->display('detail');
	}

	public function detailAction()
	{
		$user_id=getgpc('id');
		if(!$user_id)
			$this->error('参数错误');

		$model=D('User');
		if(isPost())
		{
			$data=getPost();
			if($data['password'] && $data['password']!=$data['password1'])
				$this->error('两次密码输入不一致');

			$result=$model->save($user_id,$data);

			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('用户修改成功');
		}

		$user_info=$model->getInfo($user_id);

		$title='用户详情';
		$this->assign('title',$title);

		$this->assign('info',$user_info);
		$this->display('detail');
	}
	
	public function deleteAction()
	{
		$user_id=getgpc('id');
		if(!$user_id)
			$this->error('参数错误');

		$model=D('User');
		$flag=$model->remove($user_id);
		if(!$flag)
			$this->error('删除用户失败');
		else
			$this->success('删除用户成功');
	}
	
	public function batchDeleteAction()
	{
		$result=array(
			'flag'=>false,
			'message'=>'',
		);
		
		$ids=getgpc('ids');
		$result['flag']=D('User')->remove($ids);
		
		if(!$result['flag'])
		{
			$result['message']='删除失败';
		}

		$result=json_encode($result);
		echo $result;
	}
}
