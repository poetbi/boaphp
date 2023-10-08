<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.event.listener.controller.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\event\listener;

use boa\boa;
use boa\event\listener;

class controller implements listener{

	public function __construct($args){
		$mod = boa::env('mod');
		$con = boa::env('con');
		boa::log()->set('info', "Controller [$mod.$con] instantiated");
	}

	public function get(){
		return true;
	}
}
?>