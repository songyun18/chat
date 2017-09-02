<?php
defined('IN_PHPFRAME') or exit('No permission resources.'); 

Base::loadAppClass('UserController');

class messageController extends UserController
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function indexAction()
	{
		$chat_id=getgpc('chat_id');
		if(!$chat_id)
			$this->error('参数错误');
		$user_id=$this->userId;
		
		$filter['chat_id']=$chat_id;
		$model=D('Message');
		$result=$model->getList($filter);
		foreach($result['list'] as $key=>$row)
		{
			if($row['user_id']==$user_id)	
				$row['is_me']=true;
			else
				$row['is_me']=false;

			$result['list'][$key]=$row;
		}
		$result['list']=array_reverse($result['list']);
		
		$this->success($result);
	}
	
	public function addAction()
	{
		$data=array();
		$data['chat_id']=getgpc('chat_id');
		$data['user_id']=$this->userId;
		$data['message']=getgpc('message');
		
		$model=D('Message');
		$result=$model->add($data);
		if(!$result['flag'])
			$this->error($result['message']);
		else
			$this->success();
	}
}
