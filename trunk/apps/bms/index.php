<?php
/**
 * index.php 入口
 */
define('APP_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('ATTMS_PATH', APP_PATH . 'attms' . DIRECTORY_SEPARATOR);
define('PHPFRAME_PATH', dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR);
include PHPFRAME_PATH . '/phpframe/base.php';
define('CSS_URL',ATTMS_URL.'bms/css/');
define('JS_URL',ATTMS_URL.'bms/js/');

Base::run();

?>
