<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.cache.cacher.permission.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\cache\cacher;

use boa\msg;
use boa\cache\cacher;

class permission implements cacher{
	private $perms = [];

	public function __construct($args){
		$group = $args['group'] ? '-'. $args['group'] : '';
		$file = BS_WWW ."cfg/perm$group.php";
		if(file_exists($file)){
			$perms = include($file);
			if($perms['allow']){
				$this->perm('allow', $perms['allow']);
			}
			if($perms['deny']){
				$this->perm('deny', $perms['deny']);
			}
		}else{
			msg::set('boa.error.2', $file);
		}
	}

	public function get(){
		return $this->perms;
	}

	private function perm($type, $str){
		$arr = explode(',', $str);
		foreach($arr as $k => $v){
			$v = trim($v);
			$this->perms[$type][$k] = $this->act($v);
		}
	}

	private function act($act){
		$num = 2 - substr_count($act, '.');
		if($num > 0){
			$act .= str_repeat('.*', $num);
		}
		return $act;
	}
}
?>