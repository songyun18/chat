<?php

/**
 *  DbFactory.class.php 数据库工厂类
 *
 * @copyright			(C) 2005-2010 PHPCMS
 * @license				http://www.phpcms.cn/license/
 * @lastmodify			2010-6-1
 */
final class DbFactory
{

    /**
     * 当前数据库工厂类静态实例
     */
    private static $db_factory;

    /**
     * 数据库配置列表
     */
    protected $db_config = array();

    /**
     * 数据库操作实例化列表
     */
    protected $db_list = array();

    /**
     * 构造函数
     */
    public function __construct()
    {}

    /**
     * 返回当前终级类对象的实例
     *
     * @param $db_config 数据库配置            
     * @return object
     */
    public static function get_instance($db_config = '')
    {
        if ($db_config == '') {
            $db_config = Base::loadConfig('database');
        }
        if (DbFactory::$db_factory == '') {
            DbFactory::$db_factory = new DbFactory();
        }
        if ($db_config != '' && $db_config != DbFactory::$db_factory->db_config)
            DbFactory::$db_factory->db_config = array_merge($db_config, DbFactory::$db_factory->db_config);
        return DbFactory::$db_factory;
    }

    /**
     * 获取数据库操作实例
     *
     * @param $db_name 数据库配置名称            
     */
    public function get_database($db_name)
    {
        if (! isset($this->db_list[$db_name]) || ! is_object($this->db_list[$db_name])) {
            $this->db_list[$db_name] = $this->connect($db_name);
        }
        return $this->db_list[$db_name];
    }

    /**
     * 加载数据库驱动
     *
     * @param $db_name 数据库配置名称            
     * @return object
     */
    public function connect($db_name)
    {
        $object = null;
        switch ($this->db_config[$db_name]['type']) {
            case 'mysql':
                Base::loadSysClass('Mysql', '', 0);
                $object = new Mysql();
                break;
            case 'mysqli':
                $object = Base::loadSysClass('mysqli');
                break;
            case 'access':
                $object = Base::loadSysClass('db_access');
                break;
            default:
                Base::loadSysClass('Mysql', '', 0);
                $object = new Mysql();
        }
        $object->open($this->db_config[$db_name]);
        return $object;
    }

    /**
     * 关闭数据库连接
     *
     * @return void
     */
    protected function close()
    {
        foreach ($this->db_list as $db) {
            $db->close();
        }
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        $this->close();
    }
}
?>
