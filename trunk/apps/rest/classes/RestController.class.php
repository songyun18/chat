<?php
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadSysClass('Controller');


class RestController extends Controller
{
	protected $userId=0;
	protected $data=array();
	
	public function __construct()
	{
		//$this->_init();
		$this->sessionInit();
		if (!empty($_SESSION['user_id']))
		{
			$this->userId=$_SESSION['user_id'];
			$this->userName=$_SESSION['user_name'];
		}
	}
	
	protected function _init()
	{
		$data = file_get_contents('php://input');
		$data = json_decode($data, true);
		if(is_null($data))
		{
			$this->error('参数解析发生错误',-1);
		}
		$_POST = $data;
	}

	public function success($data=array())
	{
		$this->display(0,'',$data);
	}
	
	public function error($message,$code,$data)
	{
		if(!$code)
			$code=10;
		$this->display($code,$message,$data);
	}

	public function display($code,$message,$data)
	{
		$result=array(
			'code'=>$code,
			'message'=>$message,
			'data'=>$data,
		);
		//if($data) $result['data']=$data;
		
		$result=json_encode($result,JSON_UNESCAPED_UNICODE);
		die($result);
	}

	protected function checkLogin()
	{
		if(!$this->userId)
			$this->error('用户未登录',-2);
	}
}
