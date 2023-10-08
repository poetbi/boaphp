<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.event.listener.init.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\event\listener;

use boa\boa;
use boa\event\listener;

class init implements listener{
	public function __construct($args){
		boa::log()->set('info', 'System initialized');
	}

	public function get(){
		return true;
	}
}
?>