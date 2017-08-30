<?php 
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadAppClass('SystemController');

class commentController extends SystemController
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function indexAction()
	{
		$filter=array();
		//$filter['title']=getgpc('title');
		$filter['title']=getgpc('title');
		$filter['user_name']=getgpc('user_name');
		$filter['content']=getgpc('content');
		
		$this->assign('filter',$filter);
		
		$commentModel=D('Comment');
		$result=$commentModel->getList($filter);
		
		$this->assign('list',$result['list']);
		$this->assign('page_info',$result['page']);
		
		$this->assign('title','评论列表');
		$this->display();
	}
	
	public function addAction()
	{
		if(isPost())
		{
			//$this->upload();
			$data=getPost();
			
			$model=D('Comment');
			$result=$model->add($data);
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('添加记录成功',pcUrl('comment','index'));
		}
		
		$this->assign('title','添加记录');
		$this->display('detail');
	}
	
	public function detailAction()
	{
		$comment_id=getgpc('id');
		if(!$comment_id)
			$this->error('参数错误');
		
		$model=D('Comment');
		if(isPost())
		{
			//$this->upload();
			$data=getPost();
			$result=$model->update($comment_id,$data);
			
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->success('修改记录成功',pcUrl('comment','index'));
		}
		
		$info=$model->getInfo($comment_id);
		$this->assign('info',$info);
		
		$this->assign('title','修改记录');
		$this->display('detail');
	}
	
	public function deleteAction()
	{
		$comment_id=getgpc('id');
		if(!$comment_id)
			$this->error('参数错误');
		
		$flag=D('Comment')->remove($comment_id);
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
		$result['flag']=D('Comment')->remove($ids);
		
		if(!$result['flag'])
		{
			$result['message']='删除失败';
		}

		$result=json_encode($result);
		echo $result;
	}
}
