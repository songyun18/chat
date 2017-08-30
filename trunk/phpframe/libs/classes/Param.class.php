<?php

/**
 *  param.class.php	参数处理类
 *
 * @lastmodify			2012-9-17
 */
class Param
{
	private $defaultRoute;
	private $pathInfo;
	
	public function __construct()
	{
		$this->defaultRoute=Base::loadConfig('route','default');
		$path_info=Base::loadConfig('system','path_info');
		if($path_info && $_SERVER['PATH_INFO'])
		{
			$this->pathInfo=explode('/',$_SERVER['PATH_INFO']);
			$_GET['c']=$this->pathInfo[1];
			$_GET['a']=$this->pathInfo[2];
			for($i=3;$i<count($this->pathInfo);$i++)
			{
				$temp=$this->pathInfo[$i];
				$temp=explode('_',$temp);
				$_GET[$temp[0]]=$temp[1];
			}
		}
	}
	
	public function routeC()
	{
		$route_c=getgpc('c');
		if(!$route_c)
			$route_c=$this->defaultRoute['c'];
		
		return $route_c;
	}
	
	public function routeA()
	{
		$route_a=getgpc('a');
		if(!$route_a)
			$route_a=$this->defaultRoute['a'];
		
		return $route_a;
	}
}
?>
