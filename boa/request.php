<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.request.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class request{
	private $rule = [];
	private $var = [];
	private $act = null;

	public function __construct($act = null){
		if(!$act){
			$act = boa::env('act');
		}
		$this->act = $act;
	}

	public function __get($k){
		return $this->v($k);
	}

	public function __set($k, $v){
		$this->var[$k] = $v;
	}

	public function validate(){
		$rules = $this->rule();
		foreach($rules as $key => $rule){
			$this->v($key);
		}
	}

	public function raw(){
		return file_get_contents('php://input');
	}
	
	public function v($key){
		$call = boa::info('call');
		if($call == 1 && array_key_exists($key, $this->var)){
			return $this->var[$key];
		}

		$vars = boa::env('var');
		switch(true){
			case array_key_exists($key, $vars):
				$val = $vars[$key];
				break;

			case array_key_exists($key, $_POST):
				$val = $_POST[$key];
				break;

			case array_key_exists($key, $_GET):
				$val = $_GET[$key];
				break;

			case array_key_exists($key, $_COOKIE):
				$cookie = boa::cookie();
				$val = $cookie->get($key);
				break;

			default:
				$val = null;
		}

		$rule = $this->rule($key);

		if(!array_key_exists('filter', $rule) && defined('FILTER')){
			$rule['filter'] = FILTER;
		}

		$val = boa::validater()->execute($key, $val, $rule);
		$this->var[$key] = $val;
		return $val;
	}

	private function rule($key = null){
		if($this->act){
			$this->load();
		}
		if($key){
			if(array_key_exists($key, $this->rule)){
				return $this->rule[$key];
			}else{
				return [];
			}
		}else{
			return $this->rule;
		}
	}

	private function load(){
		$mod = boa::env('mod');
		$con = boa::env('con');
		$act = $this->act;
		$file = BS_MOD ."$mod/variable/$con/$act.php";
		if(file_exists($file)){
			$this->rule = include($file);
			boa::log()->set('info', "Validater rule [$mod.$con.$act] loaded");
		}else{
			$this->rule = [null];
		}
		$this->act = null;
	}
}
?>