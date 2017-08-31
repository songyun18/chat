<?php 
Base::loadSysClass('Model');

class MessageModel extends Model
{
	public function __construct($table_name='',$table_pre='')
	{
		parent::__construct($table_name,$table_pre);

		$fields=array(
			'chat_id'=>array(
				'required'=>true,
				'add'=>true,
				'number'=>true,
				'name'=>'聊天主键',
			),
			'user_id'=>array(
				'required'=>true,
				'add'=>true,
				'number'=>true,
				'name'=>'用户id',
			),
			'message'=>array(
				'required'=>true,
				'strlen'=>array(1,255),
				'checkjs'=>true,
				'name'=>'内容',
			),
			'is_read'=>array(
				'checkjs'=>true,
				'array'=>array(0,1),
				'name'=>'是否阅读',
			),
		);
		$this->setFields($fields);
	}

	public function getList($filter=array(),$page_size=10)
	{
		$where=' 1 ';

		if(isset($filter['chat_id']))
			$where.=' and a.chat_id ='.$filter['chat_id'];

		$count  = $this->getCount($where);    //计算总数
		$page   = new Page($count, $page_size);

		$select="a.*,b.user_name,b.avatar";
		$order = 'message_id desc';
		$limit=$page->firstRow.','.$page->listRows;

		$result=array();
		$sql="select $select from #@_message as a
			left join #@_user as b on b.user_id=a.user_id
			where $where order by $order limit $limit";

		$result['list']=$this->query($sql);
		$default_avatar=ATTMS_URL.'web/images/avatar.jpg';
		foreach($result['list'] as $key=>$row)
		{
			if(!$row['avatar'])
			{
				$row['avatar']=$default_avatar;
			}
			switch($row['is_read'])
			{
				case 0:
					$row['is_read_exp']='否';
					break;
				case 1:
					$row['is_read_exp']='是';
					break;
			}
			$result['list'][$key]=$row;
		}

		$result['page']=$page->getPageInfo();
		$result['next_page']=$page->getNextPage();
		return $result;
	}


	public function getCount($where)
	{
		$sql="select count(*) as count from #@_message as a
			left join #@_user as b on b.user_id=a.user_id
			where $where";
		$temp=$this->query($sql);
		return $temp[0]['count'];
	}

	public function getInfo($message_id)
	{
		$select="a.*,b.user_name,b.avatar";
		$sql="select $select from #@_message as a
			left join #@_user as b on b.user_id=a.user_id
			where a.message_id=".$message_id;
		$default_avatar=ATTMS_URL.'web/images/avatar.jpg';
		if($info)
		{
			$info=$info[0];
			
			if(!$info['avatar'])
			{
				$info['avatar']=$default_avatar;
			}
		}

		return $info;
	}

	//添加记录
	public function add($data)
	{
		$result=$this->checkPost($data);
		if(!$result['flag'])
			return $result;

		$data1=$result['data'];
		unset($result['data']);
		$result['flag']=parent::add($data1);
		if(!$result['flag'])
			$result['message']='插入记录失败';

		return $result;
	}

	//修改记录
	public function update($message_id,$data)
	{
		$result=$this->checkPost($data,'update');
		if(!$result['flag'])
			return $result;

		$data1=$result['data'];
		unset($result['data']);
		$result['flag']=false;

		$info=$this->getInfo($message_id);
		if(!$info)
		{
			$result['message_id错误'];
			return $result;
		}

		$result['flag']=$this->where(array(
			'message_id'=>$message_id			
		))->save($data1);

		if(!$result['flag'])
			$result['message']='修改记录失败';

		return $result;
	}

	public function remove($message_id)
	{
		$flag=$this->where(array(
			'message_id'=>$message_id		
		))->delete();

		return $flag;
	}

}
