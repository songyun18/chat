<?php
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadSysClass('Controller');


class RestController extends Controller
{
	protected $userId=0;
	protected $data=array();
	
	public function __construct()
	{
		$this->_init();
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
	
	protected function upload($watermark_enable=0,$thumb_setting=array(),$is_ajax=false,$max_size=0)
	{
		$result=array(
			'flag'	=>false,
			'message'=>''
		);
		
		$upload_url = Base::loadConfig('system','upload_url');
		$upload_path = Base::loadConfig('system','upload_path');
		$upload_file_type=array(
			'jpg',
			'gif',
			'png',
		);
		
		if(!count($_FILES))
		{
			$result['message']='FILES数组为空';
			return $result;
		}
		
		$index=0;
		$now=time();
		
		foreach($_FILES as $key=>$row)
		{
			$index++;
			if($row['name']=='') continue;
			//得到文件名
			$file_ext=explode('.',$row['name']);
			$file_ext=strtolower(array_pop($file_ext));
			if(!in_array($file_ext,$upload_file_type))
			{
				$result['message']='上传文件类型错误';
				return $result;
			}
			
			$file_name=date('YmdHis',$now).$index.'.'.$file_ext;
			//文件的相对路径
			$path1=date('Y');
			$path2=date('m');
			$path=$path1.'/'.$path2.'/';
			$file_path=$upload_path.$path;

			//检查文件夹是否存在
			if(!file_exists($file_path))
			{
				$result['flag']=mkdir($file_path,0777,true);
				if(!$result['flag'])
				{
					$result['message']='文件夹新建失败';
					return $result;
				}
			}
			
			//移动文件
			$file_path.=$file_name;
			$result['flag']=move_uploaded_file($row['tmp_name'],$file_path);
			if(!$result['flag'])
			{
				$result['message']='文件上传失败';
				return $result;
			}
			
			$file_url=$upload_url.$path.$file_name;
			//$file_url=$path.$file_name;
			$_POST[$key]=$file_url;
		}

		$result['flag']=true;
		return $result;
		
		/*
		$upload_url = pc_base::load_config('system','upload_url');
		$upload_file_type='jpg|gif|png';
		
		if(!count($_FILES)) return false;
		foreach($_FILES as $key=>$row)
		{
			if($row['name']=='') continue;
			$Attachment = pc_base::load_sys_class('attachment');
			$rs=$Attachment->upload($key,$upload_file_type,$max_size,0,$thumb_setting,$watermark_enable);
			
			if($rs===false)
			{
				if(!$is_ajax)
					$this->error($Attachment->error());
				else
				{
					$result['message']=$Attachment->error();
					return $result;
				}
			}
			
			if(is_array($rs))
			{
				if(!isset($_POST[$key]))
					$_POST[$key]=array();
				foreach($rs as $k=>$val)
				{
					if($val)
						//$_POST[$key][$k]=$upload_url.$val;
						$_POST[$key][$k]=$val;
				}
			}
			else
				//$_POST[$key]=$upload_url.$rs;
				$_POST[$key]=$rs;
			$result['flag']=true;
		}
		return $result;
		*/
	}
}
