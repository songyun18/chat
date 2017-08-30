<?php
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadAppClass('SystemController');

class configController extends SystemController
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function indexAction()
	{
		$black_list=array();
		$model=D('Config');
		
		if(isPost())
		{
			$data=getPost();	
			foreach($data as $key=>$value)
			{
				if(in_array($key,$black_list))
					unset($data[$key]);
			}
			
			$result=$model->update($data);
			
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('网站设置修改成功',pcUrl('config','index'));
			
		}
		
		$result=$model->select();
		foreach($result as $key=>$row)
		{
			if(in_array($row['key'],$black_list))
				unset($result[$key]);
		}
		$config_list=array_values($result);
		$this->assign('config_list',$config_list);
		
		$this->display();
	}
}
