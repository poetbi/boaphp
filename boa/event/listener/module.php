<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.event.listener.module.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\event\listener;

use boa\boa;
use boa\event\listener;

class module implements listener{

	public function __construct($args){
		$mod = boa::env('mod');
		boa::log()->set('info', "Module [$mod] initialized");
	}

	public function get(){
		return true;
	}
}
?>