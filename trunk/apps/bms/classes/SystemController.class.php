<?php
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadSysClass('Controller');

class SystemController extends Controller
{
	protected $userId;
	protected $power;
	protected $menuList;
	public function __construct()
	{
		$this->sessionInit();
		
		if(!isset($_SESSION['uinfo']['userid']))
		{
			$this->error('请登录',pcUrl('index','login'));
		}
		$this->layout='common';
		
		$this->userId=$_SESSION['uinfo']['userid'];
		$this->power=intval($_SESSION['uinfo']['power']);
		
		$this->menuList = $this->getMenu();
		$this->assign("menu_list",$this->menuList);
		$this->_checkPower();

		$site_name=D('Config')->getValue('site_name');
		$this->assign('site_name',$site_name);
		$this->assign('power',$this->power);
		parent::__construct();
	}

	//检查权限
	private function _checkPower()
	{
		$controller=ROUTE_C;
		$need_power=100;
		foreach($this->menuList as $row)
		{
			if($row['c']==$controller)
			{
				$need_power=$row['power'];
				break;
			}
			
			foreach($row['children'] as $row1)
			{
				if($row1['c']==$controller)
				{
					$need_power=$row1['power'];
					break 2;
				}
			}
		}
		if($need_power<$this->power)
			$this->error('权限不足');
	}
	
	/**
	 * 获取菜单
	 */
	public function getMenu()
	{
		$menu_list=D('AdminMenu')->getAllMenu();
		
		foreach($menu_list as $key=>$row)
		{
			if($row['power']<$this->power)
				unset($menu_list[$key]);
			else
			{
				foreach($row['children'] as $key1=>$row1)
				{
					if($row1['power']<$this->power)
						unset($menu_list[$key]['children'][$key1]);
				}
			}
			
		}
		return $menu_list;
	}
	
	public function upload($watermark_enable=0,$thumb_setting=array(),$is_ajax=false,$max_size=0)
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
			
			//$file_url=$upload_url.$path.$file_name;
			$file_url=$path.$file_name;
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

	protected function _export($data,$file_name="data.xls")
	{
		if($this->power!==0)
			$this->error('权限不足');
		
		//pc_base::load_sys_class('PHPExcel');
		require_once(PHPFRAME_PATH.'phpframe/libs/classes/PHPExcel.php');
		require_once(PHPFRAME_PATH.'phpframe/libs/classes/PHPExcel/Writer/Excel5.php');
		
		// 首先创建一个新的对象  PHPExcel object
		$objPHPExcel = new PHPExcel();

		// 设置文件的一些属性，在xls文件——>属性——>详细信息里可以看到这些值，xml表格里是没有这些值的
		$objPHPExcel
			  ->getProperties()  //获得文件属性对象，给下文提供设置资源
			  ->setCreator( "Maarten Balliauw")                 //设置文件的创建者
			  ->setLastModifiedBy( "Maarten Balliauw")          //设置最后修改者
			  ->setTitle( "Office 2007 XLSX Test Document" )    //设置标题
			  ->setSubject( "Office 2007 XLSX Test Document" )  //设置主题
			  ->setDescription( "Test document for Office 2007 XLSX, generated using PHP classes.") //设置备注
			  ->setKeywords( "office 2007 openxml php")        //设置标记
			  ->setCategory( "Test result file");                //设置类别
		// 位置aaa  *为下文代码位置提供锚
		/*
		// 给表格添加数据
		$objPHPExcel->setActiveSheetIndex(0)             //设置第一个内置表（一个xls文件里可以有多个表）为活动的
					->setCellValue( 'A1', 'Hello' )         //给表的单元格设置数据
					->setCellValue( 'B2', 'world!' )      //数据格式可以为字符串
					->setCellValue( 'C1', 12)            //数字型
					->setCellValue( 'D2', 12)            //
					->setCellValue( 'D3', true )           //布尔型
					->setCellValue( 'D4', '=SUM(C1:D2)' );//公式
		*/
		$obj=$objPHPExcel->setActiveSheetIndex(0);
		//设置标题
		$index=0;
		foreach($data[0] as $key=>$row)
		{
			$keygen=65+$index;
			
			if($index>25)
				$keygen='A'.chr($keygen-26);
			else
				$keygen=chr($keygen);
			$obj->setCellValue($keygen.'1',$key);
			$index++;
		}
		$j=1;
		foreach($data as $row)
		{
			$index=0;
			$j++;
			foreach($row as $value)
			{
				$keygen=65+$index;
				
				if($index>25)
					$keygen='A'.chr($keygen-26);
				else
					$keygen=chr($keygen);
				
				$obj->setCellValue($keygen.$j,$value);
				$index++;
			}
		}

		//得到当前活动的表,注意下文教程中会经常用到$objActSheet
		$objActSheet = $objPHPExcel->getActiveSheet();
		
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="data.xls"');
		header('Cache-Control: max-age=0');
		
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);;
		$objWriter->save('php://output');
		exit;
	}
	
	public function errorJSON($message)
	{
		$this->displayJSON(false,$message);
	}
	
	public function successJSON($message)
	{
		$this->displayJSON(true,$message);
	}

	public function displayJSON($flag,$message)
	{
		$result=array(
			'flag'=>$flag,
			'message'=>$message,
		);
		$result=json_encode($result);
		echo $result;
		die();
	}
}
