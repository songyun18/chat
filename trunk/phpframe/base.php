<?php
/**
 *  base.php PHPFRAME入口文件
 *
 * @lastmodify			2010-6-7
 */
define('IN_PHPFRAME', true);
// PHPFRAME框架路径
define('PC_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

if (! defined('PHPFRAME_PATH'))
    define('PHPFRAME_PATH', dirname(PC_PATH) . DIRECTORY_SEPARATOR);

    // 缓存文件夹地址
define('CONFIGS_PATH', PHPFRAME_PATH . 'configs' . DIRECTORY_SEPARATOR);
// 主机协议
define('SITE_PROTOCOL', isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://');
// 当前访问的主机名
define('SITE_URL', (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''));
// 来源
define('HTTP_REFERER', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');

// 系统开始时间
define('SYS_START_TIME', microtime());

// 加载公用函数库
Base::loadSysFunction('global');
Base::loadSysFunction('extension');
Base::autoLoadFunction();
Base::loadConfig('system', 'errorlog') ? set_error_handler('my_error_handler') : error_reporting(E_ERROR | E_WARNING | E_PARSE);
define('CHARSET', Base::loadConfig('system', 'charset'));
// 输出页面字符集
header('Content-type: text/html; charset=' . CHARSET);
define('SYS_TIME', time());

// 设置本地时差
function_exists('date_default_timezone_set') && date_default_timezone_set(Base::loadConfig('system', 'timezone'));

// upload路径
define('UPLOAD_URL', Base::loadConfig('system', 'upload_url'));
// 静态资源路径
define('ATTMS_URL', Base::loadConfig('system', 'attms_url'));
// 静态资源路径
define('UPLOAD_PATH', Base::loadConfig('system', 'upload_path'));
// 应用静态文件路径
define('PLUGIN_STATICS_PATH', WEB_PATH . 'statics/plugin/');

if (Base::loadConfig('system', 'gzip') && function_exists('ob_gzhandler')) {
    ob_start('ob_gzhandler');
} else {
    ob_start();
}

// 日志配置
define("LOG4PHP_DIR", PC_PATH . "libs/classes/log4php");
define("LOG4PHP_LOG", PHPFRAME_PATH . 'weblogs');
define("CACHE_PATH", PHPFRAME_PATH . 'cache/');
global $logsLevel;
$logsLevel = array(
    'INFO', // 正常访问日志
    'DEBUG', // 调试信息日志
    'ERROR' // 错误日志
);

class Base
{
	//系统运行
	public static function run()
	{
		try
		{
			self::loadSysClass('Application');
		}
		catch(Exception $e)
		{
			$is_debug=Base::loadConfig('system','debug');
			if(!$is_debug)
				exit();
			
			$template='<html>
				<head>
				<meta http-equiv="content-type" content="text/html;charset=utf-8">
				<style>
				body {padding:2px; font-size:14px;}
				.container{border:1px solid black; padding:0 5px 5px 10px;}
				p { margin:0px 0; line-height:1.4em; }
				h3 { font-style:italic; margin:10px 0; }
				</style>
				</head>
				<body>
				<div class="container">
					<h3>错误提示</h3>
					<p><font color="red">%s</font></p>
					<p>%s</p>
					<p>位于%s行</p>
				</div>
				</body>
				</html>';
			echo sprintf($template,$e->getMessage(),$e->getFile(),$e->getLine());
		}

	}
	
	public static function loadSysClass($class_name,$path='',$init=1)
	{
		return self::_loadClass($class_name,$path,$init);
	}

	public static function loadAppClass($class_name,$path='',$init=1)
	{
		if(!$path)
			$path='../apps/'.ROUTE_M.'/classes';
		
		return self::_loadClass($class_name,$path,$init);
	}
	
	public static function loadLogic($class_name,$path='logic',$init=1)
	{
		return self::_loadClass($class_name.'Logic',$path,$init);
	}
	
	public static function autoLoadFunction($path='')
	{
        if (empty($path))
            $path = 'libs' . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'autoload';
        $path .= DIRECTORY_SEPARATOR . '*.function.php';
        $auto_funcs = glob(PC_PATH . DIRECTORY_SEPARATOR . $path);
        if (! empty($auto_funcs) && is_array($auto_funcs)) {
            foreach ($auto_funcs as $func_path) {
                include $func_path;
            }
        }
	}

	public static function loadSysFunction($func)
	{
		self::_loadFunction($func);
	}
	
	//加载系统函数
	private static function _loadFunction($func,$path='')
	{
        static $funcs = array();
        if (empty($path))
            $path = 'libs' . DIRECTORY_SEPARATOR . 'functions';
        $path .= DIRECTORY_SEPARATOR . $func . '.function.php';
        $key = md5($path);
        if (isset($funcs[$key]))
            return true;
        if (file_exists(PC_PATH . $path)) {
            include PC_PATH . $path;
        } else {
            $funcs[$key] = false;
            return false;
        }
        $funcs[$key] = true;
        return true;
	}
	
	//加载配置文件
	public static function loadConfig($file, $key = '', $default = '', $reload = false)
	{
        static $configs = array();
        if (! $reload && isset($configs[$file])) {
            if (empty($key)) {
                return $configs[$file];
            } elseif (isset($configs[$file][$key])) {
                return $configs[$file][$key];
            } else {
                return $default;
            }
        }
        $path = CONFIGS_PATH . $file . '.php';
        if (file_exists($path)) {
            $configs[$file] = include $path;
        }
        if (empty($key)) {
            return $configs[$file];
        } elseif (isset($configs[$file][$key])) {
            return $configs[$file][$key];
        } else {
            return $default;
        }
	}
	
	private static function _loadClass($class_name,$path='',$init=1)
	{
        static $classes = array();
        if (empty($path))
            $path = 'libs' . DIRECTORY_SEPARATOR . 'classes';
        
        $key = md5($path . $class_name);
        if (isset($classes[$key])) {
            if (! empty($classes[$key])) {
                return $classes[$key];
            } else {
                return true;
            }
        }
        if (file_exists(PC_PATH . $path . DIRECTORY_SEPARATOR . $class_name . '.class.php')) {
            include_once PC_PATH . $path . DIRECTORY_SEPARATOR . $class_name . '.class.php';
            $name = $class_name;
            if ($init) {
                $classes[$key] = new $name();
            } else {
                $classes[$key] = true;
            }
            return $classes[$key];
        } else {
            return false;
        }
		
	}
}
