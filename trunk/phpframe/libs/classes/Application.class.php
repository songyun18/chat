<?php

class Application
{

    /**
     * 构造函数
     */
    public function __construct()
    {
        $param = Base::loadSysClass('Param');
        define('ROUTE_M', basename(APP_PATH));
        define('ROUTE_C', $param->routeC());
        define('ROUTE_A', $param->routeA());
        $this->init();
    }

    /**
     * 调用件事
     */
    private function init()
    {
		$route_a=ROUTE_A.'Action';
        $controller = $this->loadController();
        if (method_exists($controller, $route_a)) {
            if (preg_match('/^[_]/i', $route_a)) {
                throw new Exception('You are visiting the action is to protect the private action');
            } else {
				call_user_func(array(
					$controller,
					$route_a
				));
            }
        } else {
            throw new Exception('Action does not exist.');
        }
    }

    /**
     * 加载控制器
     *
     * @param string $filename            
     * @param string $m            
     * @return obj
     */
    private function loadController($filename = '', $m = '')
    {
        if (empty($filename)) {
            $filename = ROUTE_C;
        }
        $filepath = APP_PATH . 'controllers' . DIRECTORY_SEPARATOR . $filename . '.php';
        if (file_exists($filepath)) {
            $classname = $filename.'Controller';
            include $filepath;
            if (class_exists($classname)) {
                return new $classname();
            } else {
                throw new Exception('Controller does not exist.');
            }
        } else {
            throw new Exception('Controller does not exist.');
        }
    }
}
