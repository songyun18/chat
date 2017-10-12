<?php 
Base::loadSysClass('Model');

class UserModel extends Model
{
	public function __construct($table_name='',$table_pre='')
	{
		parent::__construct($table_name,$table_pre);
		
		$fields=array(
			'user_name'=>array(
				'required'=>true,
				'strlen'=>array(1,255),
				'add'=>true,
				'name'=>'用户名',
			),
			'password'=>array(
				'required'=>true,
				'strlen'=>array(1,255),
				'name'=>'密码',
			),
			'nickname'=>array(
				'strlen'=>array(1,255),
				'name'=>'昵称',
			),
			'phone'=>array(
				'phone'=>true,
				'name'=>'手机号码',
			),
			'email'=>array(
				'email'=>true,
				'name'=>'email',
			),
			'qq'=>array(
				'number'=>true,
				'name'=>'qq号码',
			),
			'avatar'=>array(
				'strlen'=>array(1,255),
				'name'=>'头像',
			),
			'gender'=>array(
				'array'=>array(0,1),
				'name'=>'性别',
			),
			'user_status'=>array(
				'array'=>array(0,1),
				'name'=>'用户状态',
			),
			'last_login_time'=>array(
				'number'=>true,
				'name'=>'用户状态',
			),
			'last_login_ip'=>array(
				'strlen'=>array(1,255),
				'name'=>'用户状态',
			),
		);
		$this->setFields($fields);
	}
	
	public function getList($filter=array(),$page_size=10)
	{
		$where=' 1 ';
		
		/*
		if(isset($filter['title']))
			$where.=' and a.title like "%'.$filter['title'].'%"';
		 */
		
		$count  = $this->getCount($where);    //计算总数
		$page   = new Page($count, $page_size);

		$select="*";
		$order = 'user_id desc';
		$limit=$page->firstRow.','.$page->listRows;

		$result=array();
		$result['list']=$this->field($select)->order($order)->limit($limit)->select();

		foreach($result['list'] as $key=>$row)
		{
			$row['reg_time_exp']=date('Y-m-d H:i:s',$row['reg_time']);
			$row['last_login_time_exp']=date('Y-m-d H:i:s',$row['last_login_time_exp']);
			
			switch($row['gender'])
			{
				case 0:
					$row['gender_exp']='男';
					break;
				case 1:
					$row['gender_exp']='女';
					break;
			}
			
			switch($row['user_status'])
			{
				case 0:
					$row['user_status_exp']='未初始化';
					break;
				case 1:
					$row['user_status_exp']='已初始化';
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
		$count=$this->where($where)->count();
		return $count;
	}

	public function getInfo($user_id)
	{
		$info=$this->where(array(
			'user_id'=>$user_id			
		))->find();
		
		if($info)
		{
			$default_avatar=ATTMS_URL.'web/images/avatar.jpg';
			if(!$info['avatar'])
				$info['avatar']=$default_avatar;
			
			$info['reg_time_exp']=date('Y-m-d H:i:s',$info['reg_time']);
			$info['last_login_time_exp']=date('Y-m-d H:i:s',$info['last_login_time_exp']);
			
			switch($info['gender'])
			{
				case 0:
					$info['gender_exp']='男';
					break;
				case 1:
					$info['gender_exp']='女';
					break;
			}
			
			switch($info['user_status'])
			{
				case 0:
					$info['user_status_exp']='未初始化';
					break;
				case 1:
					$info['user_status_exp']='已初始化';
					break;
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
		
		$result['flag']=false;
		
		//检查用户名是否存在
		$count=$this->where(array(
			'user_name'=>$data['user_name']
		))->count();
		if($count)
		{
			$result['message']='用户名已经存在';
			return $result;
		}
		//检查用户密码的格式
		$preg="/^[\S]{6,}$/";
		if(!preg_match($preg,$data['password']))
		{
			$result['message']='密码至少为六位数';
			return $result;
		}
		
		$data1=array();
		$data1['user_name']=$data['user_name'];
		$data1['encrypt']=to_rand(8);
		$data1['password']=md5(md5($data['password']).$data1['encrypt']);
		$data1['nickname']=$data['nickname']?$data['nickname']:$data['user_name'];
		$data1['phone']=$data['phone'];
		$data1['qq']=$data['qq'];
		$data1['email']=$data['email'];
		$data1['avatar']=$data['avatar'];
		$data1['gender']=$data['gender'];
		$data1['reg_time']=$data['reg_time'];
		
		$result['flag']=$this->insert($data1);
		if(!$result['flag'])
		{
			$result['message']='写入用户表失败';
			return $result;
		}
		$user_id=$this->getInsertId();
		
		return $result;
	}

	//修改记录
	public function save($user_id,$data)
	{
		$result=$this->checkPost($data,'save');
		if(!$result['flag'])
			return $result;

		$data1=$result['data'];
		unset($result['data']);
		$result['flag']=false;

		$info=$this->getInfo($user_id);
		if(!$info)
		{
			$result['user_id错误'];
			return $result;
		}
		
		$result['flag']=$this->where(array(
			'user_id'=>$user_id			
		))->update($data1);

		if(!$result['flag'])
			$result['message']='修改记录失败';

		return $result;
	}

	public function remove($user_id)
	{
		$flag=$this->where(array(
			'user_id'=>$user_id		
		))->delete();

		return $flag;
	}
	
	public function login($user_name,$password)
	{
		$result=array(
			'flag'=>false,
			'message'=>'',
		);
		
		$info=$this->where(array('user_name'=>$user_name))->find();
		if(!$info)
		{
			$result['message']='账户不存在';
			return $result;
		}
		
		if($info['password']!=md5(md5($password).$info['encrypt']))
		{
			$result['message']='密码错误';
			return $result;
		}
		
		//更新登录信息
		$data=array(
			"last_login_ip"=>ip(),
			"last_login_time"=>time(),
		);
		$result['flag']=$this->where(array('user_id'=>$info['user_id']))->update($data);
		if(!$result['flag'])
		{
			$result['message']='登录失败';
			return $result;
		}
		
		session_start();
		$_SESSION['user_id']=$info['user_id'];
		$_SESSION['user_name']=$info['user_name'];
		
		$default_avatar=ATTMS_URL.'web/images/avatar.jpg';
		if(!$info['avatar'])
			$info['avatar']=$default_avatar;
		
		$result['data']=array(
			'user_id'=>$info['user_id'],
			'user_name'=>$info['user_name'],
			'nickname'=>$info['nickname'],
			'avatar'=>$info['avatar'],
		);
		return $result;
	}

	public function logout()
	{
		session_start();
		unset($_SESSION['user_id']);
		unset($_SESSION['user_name']);
	}
}
