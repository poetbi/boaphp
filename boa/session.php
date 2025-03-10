<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.session.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class session{
	private $obj;

	public function __construct($cfg = []){
		if(!array_key_exists('driver', $cfg)) $cfg['driver'] = 'file';

		$driver = '\\boa\\session\\driver\\'. $cfg['driver'];
		$this->obj = new $driver($cfg);
	}
	
	public function ttl($second){
		if($second > 0){
			$cookie = boa::cookie();
			$cookie->cfg('prefix', '');
			$cookie->set(session_name(), $this->sid(), $second);
		}
	}

	public function sid(){
		return session_id();
	}

	public function get($key){
		$val = null;
		$arr = explode('.', $key);
		if(array_key_exists($arr[0], $_SESSION)){
			$val = $_SESSION[$arr[0]];
			$num = count($arr);
			for($i = 1; $i < $num; $i++){
				$val = $val[$arr[$i]];
			}
		}
		return $val;
	}

	public function set($key, $val){
		$arr = explode('.', $key);
		$obj = &$_SESSION[$arr[0]];
		$num = count($arr);
		for($i = 1; $i < $num; $i++){
			$obj = &$obj[$arr[$i]];
		}
		$obj = $val;
	}

	public function del($key){
		$arr = explode('.', $key);
		$obj = &$_SESSION;
		$num = count($arr);
		for($i = 0; $i < $num - 1; $i++){
			$obj = &$obj[$arr[$i]];
		}
		unset($obj[$arr[$num -1]]);
	}

	public function gc(){
		if(function_exists('session_gc')){
			return session_gc();
		}else{
			return false;
		}
	}

	public function save(){
		return session_write_close();
	}

	public function clear(){
		unset($_SESSION);
	    return session_destroy();
	}
}