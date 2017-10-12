<?php
Base::loadSysClass('Model');

class AdminModel extends Model
{
	public function __construct($table_name='',$table_pre='')
	{
		parent::__construct($table_name,$table_pre);

		$fields=array(
			'username'=>array(
				'required'=>true,
				'strlen'=>array(4,255),
				'add'=>true,
				'name'=>'管理员名',
			),
			'password'=>array(
				'required'=>true,
				'strlen'=>array(1,255),
				'name'=>'密码',
			),
			'email'=>array(
				'email'=>true,
				'name'=>'邮箱',
			),
			'realname'=>array(
				'strlen'=>array(1,255),
				'name'=>'真实姓名',
			),
			'power'=>array(
				'array'=>array(0,1,2),
				'name'=>'权限',
			),

		);
		$this->setFields($fields);
	}
		
	public function getList($filter=array(),$page_size=10)
	{
		$where=' 1 ';
		
		if(isset($filter['power']) && $filter['power']!=-1)
			$where.=' and power='.$filter['power'];
		if(isset($filter['username']))
			$where.=' and username like "%'.$filter['username'].'%"';
		
		$count  = $this->getCount($where);    //计算总数
		$page   = new Page($count, $page_size);
		
		$select="*";
		$order = 'userid desc';
		$limit=$page->firstRow.','.$page->listRows;
		
		$result=array();
		$result['list']=$this->where($where)->order($order)->limit($limit)->select();
		foreach($result['list'] as &$row)
		{
			if($row['lastlogintime'])
				$row['lastlogintime_exp']=date('Y-m-d H:i:s',$row['lastlogintime']);
			
			switch($row['power'])
			{
				case 0:
					$row['power_exp']='超级管理员';
					break;
				case 1:
					$row['power_exp']='普通管理员';
					break;
				case 2:
					$row['power_exp']='商家帐号';
					break;
			}
		}
		
		$result['page']=$page->getPageInfo();
		return $result;
	}
	
	public function getCount($where)
	{
		return $this->where($where)->count();
	}

	//获得管理员信息
	//入口		$id		管理员主键
	//返回		$info
	public function getInfo($userid)
	{
		$info=$this->where(array(
			'userid'=>$userid
		))->find();
		if($info)
		{
			if($info['lastlogintime'])
				$info['lastlogintime_exp']=date('Y-m-d H:i:s',$info['lastlogintime']);
			
			switch($info['power'])
			{
				case 0:
					$info['power_exp']='超级管理员';
					break;
				case 1:
					$info['power_exp']='普通管理员';
					break;
				case 2:
					$info['power_exp']='商家帐号';
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
		
		$data1=$result['data'];
		unset($result['data']);
		$result['flag']=false;
		
		//检查username是否重复
		$flag=$this->where(array(
			'username'=>$data1['username']
		))->count();
		if($flag)
		{
			$result['message']='管理员名已存在';
			return $result;
		}

		$data1['encrypt']=to_rand(6);
		$data1['password']=md5(md5($data1['password']).$data1['encrypt']);

		$data1['lastlogintime']=0;
		$data1['lastloginip']='';
		
		$result['flag']=$this->insert($data1);
		if(!$result['flag'])
			$result['message']='添加记录失败';
		
		return $result;
	}
	
	//修改记录
	public function save($userid,$data)
	{
		$result=$this->checkPost($data,'save');
		if(!$result['flag'])
			return $result;
		
		$data1=$result['data'];
		unset($result['data']);
		$result['flag']=false;

		$user_info=$this->getInfo($userid);
		if(!$user_info)
		{
			$result['userid错误'];
			return $result;
		}
		if($data1['password'])
			$data1['password']=md5(md5($data1['password']).$user_info['encrypt']);
		else
			unset($data1['password']);
		
		$result['flag']=$this->where(array(
			'userid'=>$userid
		))->update($data1);
		
		if(!$result['flag'])
			$result['message']='修改记录失败';

		return $result;
	}
	
	public function login($username,$password)
	{
		$result=array(
			'flag'=>false,
			'message'=>'',
		);
		
		$info=$this->where(array('username'=>$username))->find();
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
			"lastloginip"=>ip(),
			"lastlogintime"=>time(),
		);
		$this->where(array('userid'=>$info['userid']))->update($data);
		
		$_SESSION['uinfo']=array(
			'userid'=>$info['userid'],
			'username'=>$info['username'],
			'power'=>$info['power']
		);
		$result['flag']=true;
		return $result;
	}

	public function logout()
	{
		unset($_SESSION['uinfo']);
		return true;
	}
}
