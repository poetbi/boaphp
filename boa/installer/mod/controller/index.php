<?php
namespace mod\home\controller;

use boa\boa;
use boa\msg;
use boa\controller;

class index extends controller{
	public function __construct(){
		parent::__construct();
	}

	public function index(){
		$this->view->assign('title', 'home');
		$this->view->html();
	}
}
?>