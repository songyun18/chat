<?php
defined('IN_PHPFRAME') or exit('No permission resources.'); 

Base::loadAppClass('UserController');

class mailController extends UserController
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function indexAction()
	{
		$filter=array();
		
		$user_id=$this->userId;
		$filter['user_id']=$user_id;
		
		$model=D('Mail');
		$result=$model->getList($filter);
		$this->success($result);
	}

	//操作邮件
	public function statusAction()
	{
		$status=getgpc('status');
		$mail_id=getgpc('mail_id');
		if(!$mail_id || $status==0)
			$this->error('参数错误');
		
		
		$model=D('Mail');
		//检查权限
		$info=$model->getInfo($mail_id);
		if(!$info)
			$this->error('参数错误');
		if($info['user_id'] != $this->userId)
			$this->error('权限不足');
		
		//$result=$model->update($mail_id,$data);
		$result=$model->confirm($mail_id,$status);
		
		if(!$result['flag'])
			$this->error($result['message']);
		else
			$this->success();
	}
}
