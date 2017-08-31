<?php
defined('IN_PHPFRAME') or exit('No permission resources.');
Base::loadAppClass('RestController');

class UserController extends RestController
{
	public function __construct()
	{
		parent::__construct();
		if(!$this->userId)
			$this->error('用户未登录',10);
	}
}
