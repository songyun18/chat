<?php
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadSysClass('Controller');

class indexController extends Controller
{
	public function __construct()
	{
		$this->sessionInit();
		parent::__construct();
	}

	public function indexAction()
	{
		if($_SESSION['uinfo']['userid'])
			$this->jump(pcUrl('menu','index'));
		else
			$this->jump(pcUrl('index','login'));
	}

	public function loginAction()
	{
		if(isPost())
		{
			$code = trim(getgpc("checkcode"));
			$username  = trim(getgpc("username"));
			$password = trim(getgpc("password"));

			if(empty($code)||empty($username)||empty($password))
			{
				$this->error("用户名 密码 或验证码 均不能为空",pcUrl("index","login"));
			}

			if(strtolower($_SESSION['code'])!=strtolower($code))
			{
				$this->error("验证码错误",pcUrl("index","login"));
			}
			
			$this->sessionStart();
			$adminModel=D('Admin');
			$result=$adminModel->login($username,$password);
			if(!$result['flag'])
				$this->error($result['message']);
			else
				$this->jump(pcUrl('menu','index'));
		}
		
		Base::loadSysClass('Form');
		$this->assign('checkcode',Form::checkcode('checkcode',$code_len = 4, $font_size = 16, $width = 105, $height = 35, $font = '', $font_color = '#6F9043', $background = '#EEFAF6'));
		
		$site_name=D('Config')->getValue('site_name');
		$this->assign('site_name',$site_name);
		$this->display();
	}
	
	public function logoutAction()
	{
		$this->sessionStart();
		D('Admin')->logout();
		$this->jump(pcUrl('index','login'));
	}

	/**
	 * 获取验证码
	 */
	public function checkcodeAction()
	{
		$this->sessionStart();
		
		$checkcode = Base::loadSysClass('Checkcode');
		$checkcode->background='#FFFFFF';
		//$checkcode->font_color='#ffd701';
		$checkcode->doimage();
		$_SESSION['code'] = $checkcode->get_code();
	}

	//刷新缓存
	public function flushAction()
	{
		$cache = Base::loadSysClass('CacheFile');
		$flag=$cache->flush();
		if($flag)
			$this->success('清除成功');
		else
			$this->error('清除失败');
	}

	//获得地区列表，ajax
	public function regionListAction()
	{
		$region_type=getgpc('region_type');
		$parent_id=getgpc('parent_id');
		
		$where=array(
			'region_type'=>$region_type,
		);
		if($parent_id)
			$where['parent_id']=$parent_id;
		
		$region_list=M('region')
			->where($where)
			->order('region_id asc')
			->select();
		
		$region_list=json_encode($region_list);
		echo $region_list;
	}

	//获得父id
	public function parentRegionIdAction()
	{
		$region_id=getgpc('region_id');
		$ids=array();
		
		$regionModel=M('region');
		$info=$regionModel->where(array(
			'region_id'=>$region_id
		))->find();
		$rank=$info['region_type'];
		for($i=0;$i<$rank-1;$i++)
		{
			$info=$regionModel->where(array(
				'region_id'=>$info['parent_id']
			))->find();
			array_unshift($ids,$info['region_id']);
		}
		
		echo json_encode($ids);
	}
	
	public function testAction()
	{
		$data=array(
			'user_id'=>1,
			'fans_id'=>2,
		);
		$result=D('Follow')->add($data);
		var_dump($result);
	}
}
