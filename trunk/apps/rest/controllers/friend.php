<?php
defined('IN_PHPFRAME') or exit('No permission resources.'); 

Base::loadAppClass('UserController');

class friendController extends UserController
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function indexAction()
	{
		$filter=array();
		$filter['user_id']=$this->userId;
		$user_id=$this->userId;
		$model=D('Friend');
		$result=$model->getList($filter);
		$list=$result['list'];
		foreach($list as $key=>$row)
		{
			if($row['user1_id']==$user_id)
			{
				$row['user_id']=$row['user2_id'];
				$row['user_name']=$row['user2_name'];
				$row['avatar']=$row['user2_avatar'];
			}
			else
			{
				$row['user_id']=$row['user1_id'];
				$row['user_name']=$row['user1_name'];
				$row['avatar']=$row['user1_avatar'];
			}
			
			unset($row['user1_id']);
			unset($row['user1_name']);
			unset($row['user1_avatar']);
			
			unset($row['user2_id']);
			unset($row['user2_name']);
			unset($row['user2_avatar']);
			$list[$key]=$row;
		}
		
		$this->success($list);
	}
	
	public function addAction()
	{
		$data=getPost();
		$model=D('Friend');
		$result=$model->add($data);
		if(!$result['flag'])
			$this->error($result['message']);
		else
			$this->success();
	}
}
