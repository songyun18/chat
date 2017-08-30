<?php
defined('IN_PHPFRAME') or exit('No permission resources.');
/*
$session_storage = 'session_' . pc_base::load_config('system', 'session_storage');
pc_base::load_sys_class($session_storage);
*/

class Controller
{
	protected $vals=array();
	protected $layout;
	
	public function __construct()
	{
		ini_set('date.timezone','Asia/Shanghai');
	}
	
	public function assign($key,$value)
	{
		$this->vals[$key]=$value;
	}
	
	//字符串方式输出模板
	public function trace($file='',$route_c='')
	{
		@ob_start();

		foreach($this->vals as $key=>$val)
		{
			$$key=$val;
		}
		
		$file_path=T($route_c?$route_c:ROUTE_C,($file=='')?ROUTE_A:$file);
		if(!file_exists($file_path)&&$file!='')
		{
			$file_path=T('public',$file);
			
			if(!file_exists($file_path))
				throw new Exception('模板文件不存在');
		}
		require_once($file_path);
		
		return ob_get_clean ();
	}
	
	public function display($file='',$need_layout=true)
	{
		$main_content=$this->trace($file);
		
		if($need_layout && $this->layout)
		{
			$file_path=T('layout',$this->layout);
			if(!file_exists($file_path))
				throw new Exception('布局文件不存在');
			foreach($this->vals as $key=>$val)
			{
				$$key=$val;
			}
			require_once($file_path);
		}
		else
		{
			echo $main_content;
		}
	}

	public function displayJSON()
	{
		$vals=json_encode($this->vals);
		die($vals);
	}
	
	public function success($message,$url)
	{
		$this->showmessage($message,$url,'success');
	}
	
	public function error($message,$url)
	{
		$this->showmessage($message,$url,'danger');
	}

    public function showmessage($message, $url='', $code='message', $ms = 1250, $dialog = '', $returnjs = '')
    {
        $this->assign('message', $message);
		$this->assign('code',$code);
        $this->assign('url', $url);
        $this->assign('ms', $ms);
        $this->display('message',false);
		
		die();
    }
	
	public function jump($url)
	{
		header('Location: '.$url);
	}

	protected function sessionInit()
	{
		$session_storage = 'session_' . Base::loadConfig('system', 'session_storage');
		Base::loadSysClass(c2java($session_storage));
		
		//$this->sessionStart();
		$this->sessionClose();
	}

	protected function sessionStart()
	{
		session_start();
	}

	protected function sessionClose()
	{
		session_write_close();
	}
}
