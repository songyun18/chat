<?php 
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadAppClass('SystemController');

class chatController extends SystemController
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
		
		$chatModel=D('Chat');
		$result=$chatModel->getList($filter);
		
		$this->assign('list',$result['list']);
		$this->assign('page_info',$result['page']);
		
		$this->assign('title','聊天列表列表');
		$this->display();
	}

	public function addAction()
	{
		$model=D('Chat');
		if(isPost())
		{
			//$this->upload();
			$data=getPost();
			
			$result=$model->add($data);
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('添加聊天列表成功',pcUrl('chat','index'));
		}

		$check_js=$model->checkjs();
		$this->assign('check_js',$check_js);
		
		$this->assign('title','添加聊天列表');
		$this->display('detail');
	}
	
	public function detailAction()
	{
		$chat_id=getgpc('id');
		if(!$chat_id)
			$this->error('参数错误');
		
		$model=D('Chat');
		if(isPost())
		{
			//$this->upload();
			$data=getPost();
			$result=$model->save($chat_id,$data);
			
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('修改聊天列表成功',pcUrl('chat','index'));
		}
		
		$info=$model->getInfo($chat_id);
		$this->assign('info',$info);
		
		$check_js=$model->checkjs();
		$this->assign('check_js',$check_js);
		
		$this->assign('title','修改聊天列表');
		$this->display('detail');
	}

	public function deleteAction()
	{
		$chat_id=getgpc('id');
		if(!$chat_id)
			$this->error('参数错误');
		
		$flag=D('Chat')->remove($chat_id);
		if(!$flag)
			$this->error('删除失败');
		else
			$this->success('删除成功');
	}
}
