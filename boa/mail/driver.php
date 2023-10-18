<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.mail.driver.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\mail;

use boa\base;

class driver extends base{
	protected $cfg = [
		'charset' => CHARSET
	];
	
	public function __construct($cfg = []){
        parent::__construct($cfg);
	}

	public function anti_spam($header){
		if($header){
			foreach($header as $k => $v){
				$this->cfg['header'][$k] = $v;
			}
		}
	}

	protected function addrs($addrs){
		if(is_array($addrs)){
			foreach($addrs as $k => $v){
				list($addr, $name) = explode(' ', $v, 2);
				$arr[$k] = $this->addr($addr, $name);
			}
			$str = implode(',', $arr);
		}else{
			list($addr, $name) = explode(' ', $addrs, 2);
			$str = $this->addr($addr, $name);
		}
		return $str;
	}

	protected function addr($addr, $name = null){
		if($name !== null){
			$name = trim($name);
			if($name){
				$addr = "$name <$addr>";
			}
		}
		return $addr;
	}

	protected function encode($str){
		return '=?'. $this->cfg['charset'] .'?B?'. base64_encode($str) .'?=';
	}
}
?>