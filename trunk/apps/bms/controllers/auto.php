<?php
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadAppClass('SystemController');

class autoController extends SystemController
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function indexAction()
	{
		if(isPost())
		{
			$data=getPost();
			if(!$this->_checkFolder())
				$this->errorJSON('请检查文件夹权限');

			$result=$this->_process($data);
			if(!$result['flag'])
				$this->errorJSON($result['message']);
			else
				$this->successJSON('模型记录添加完毕');
		}
		
		$this->assign('title','模型自动化');
		$field_props=$this->_fieldProps();
		$this->assign('field_props',$field_props);
		
		$folder_list=$this->_getFolder();
		$this->assign('folder_list',$folder_list);
		$this->display();
	}
	
	private function _process($data)
	{
		$result=array(
			'flag'=>false,
			'message'=>'',
		);
		
		$folder_list=$this->_getFolder();
		if(!$data['model_name'])
		{
			$result['message']='model_name为必填';
			return $result;
		}

		foreach($data as $key=>$val)
		{
			$this->assign($key,$val);
		}
		
		$result=$this->trace('template_model');
		$result="<?php \n".$result;
		$file_path=$folder_list[0]['path'].ucfirst($data['model_name']).'Model.class.php';
		$result['flag']=file_put_contents($file_path,$result);
		if(!$result['flag'])
		{
			$result['message']='模型写入失败';
			return $result;
		}

		$result=$this->trace('template_controller');
		$result="<?php \n".$result;
		$file_path=$folder_list[1]['path'].$data['model_name'].'.php';
		$result['flag']=file_put_contents($file_path,$result);
		if(!$result['flag'])
		{
			$result['message']='控制器写入失败';
			return $result;
		}

		//新建目录
		$file_path=$folder_list[2]['path'].$data['model_name'].'/';
		mkdir($file_path);
		
		$result=$this->trace('template_index');
		$result=htmlspecialchars_decode($result);
		$file_path=$folder_list[2]['path'].$data['model_name'].'/index.html';
		$result['flag']=file_put_contents($file_path,$result);
		if(!$result['flag'])
		{
			$result['message']='首页模板写入失败';
			return $result;
		}
		
		$result=$this->trace('template_detail');
		$result=htmlspecialchars_decode($result);
		$file_path=$folder_list[2]['path'].$data['model_name'].'/detail.html';
		$result['flag']=file_put_contents($file_path,$result);
		if(!$result['flag'])
		{
			$result['message']='详情页模板写入失败';
			return $result;
		}
		
		
		return $result;
	}
	
	private function _checkFolder()
	{
		$folder_list=$this->_getFolder();
		$flag=true;
		
		foreach($folder_list as $row)
		{
			if(!$row['flag']) $flag=false;
		}
		
		return $flag;
	}
	
	private function _getFolder()
	{
		$folders=array(
			'phpframe/model/',
			'apps/bms/controllers/',
			'apps/bms/templates/',
		);
		$folder_list=array();
		
		foreach($folders as $key=>$row)
		{
			$temp=PHPFRAME_PATH.$row;
			$row=array(
				'path'=>$temp,
				'flag'=>is_writable($temp),
			);
			
			array_push($folder_list,$row);
		}
		
		return $folder_list;
	}
	
	private function _fieldProps()
	{
		return array(
			array(
				'name'=>'required',
				'value'=>false,
			),
			array(
				'name'=>'add',
				'value'=>false,
			),
			
			array(
				'name'=>'checkjs',
				'value'=>false,
			),
			array(
				'name'=>'email',
				'value'=>false,
			),
			array(
				'name'=>'phone',
				'value'=>false,
			),
			array(
				'name'=>'number',
				'value'=>false,
			),
			array(
				'name'=>'array',
				'value'=>true,
			),
			array(
				'name'=>'preg',
				'value'=>true,
			),
			array(
				'name'=>'strlen',
				'value'=>true,
			),
			array(
				'name'=>'range',
				'value'=>true,
			),
			array(
				'name'=>'equal',
				'value'=>true,
			),
			array(
				'name'=>'date',
				'value'=>false,
			),
			array(
				'name'=>'datetime',
				'value'=>false,
			),
			
		);
	}
}
