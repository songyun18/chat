<?php 
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadAppClass('SystemController');

class messageController extends SystemController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function indexAction()
	{
		$filter=array();
		$filter['chat_id']=getgpc('chat_id');
		$this->assign('filter',$filter);
		
		$messageModel=D('Message');
		$result=$messageModel->getList($filter);
		
		$this->assign('list',$result['list']);
		$this->assign('page_info',$result['page']);
		
		$this->assign('title','聊天内容列表');
		$this->display();
	}

	public function addAction()
	{
		$model=D('Message');
		if(isPost())
		{
			//$this->upload();
			$data=getPost();
			
			$result=$model->add($data);
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('添加聊天内容成功',pcUrl('message','index'));
		}

		$check_js=$model->checkjs();
		$this->assign('check_js',$check_js);
		
		$this->assign('title','添加聊天内容');
		$this->display('detail');
	}
	
	public function detailAction()
	{
		$message_id=getgpc('id');
		if(!$message_id)
			$this->error('参数错误');
		
		$model=D('Message');
		if(isPost())
		{
			//$this->upload();
			$data=getPost();
			$result=$model->save($message_id,$data);
			
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('修改聊天内容成功',pcUrl('message','index'));
		}
		
		$info=$model->getInfo($message_id);
		$this->assign('info',$info);
		
		$check_js=$model->checkjs();
		$this->assign('check_js',$check_js);
		
		$this->assign('title','修改聊天内容');
		$this->display('detail');
	}

	public function deleteAction()
	{
		$message_id=getgpc('id');
		if(!$message_id)
			$this->error('参数错误');
		
		$flag=D('Message')->remove($message_id);
		if(!$flag)
			$this->error('删除失败');
		else
			$this->success('删除成功');
	}
}
