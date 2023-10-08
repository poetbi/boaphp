<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.cookie.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class cookie extends base{
	protected $cfg = [
		'prefix' => 'bs_',
		'lifetime' => 0,
		'path' => '/',
		'domain' => '',
		'secure' => false,
		'httponly' => true
	];

	public function get($key){
		$key = $this->cfg['prefix'] . $key;
		$val = $_COOKIE[$key];
		if(substr($val, 0, 1) == chr(8)){
			$val = $this->dec(substr($val, 1));
		}
		return $val;
	}

	public function set($key, $val, $ttl = null, $enc = false){
		$key = $this->cfg['prefix'] . $key;
	
		if($ttl === null){
			$ttl = $this->cfg['lifetime'];
		}
		$ttl = $ttl == 0 ? 0 : time() + $ttl;
		
		if($_SERVER['SERVER_PORT'] == 443 && $this->cfg['secure']){
			$secure = true;
		}else{
			$secure = false;
		}
		
		if($enc){
			$val = $this->enc($val);
		}
		
		$_COOKIE[$key] = $val;
		return setcookie($key, $val, $ttl, $this->cfg['path'], $this->cfg['domain'], $secure, $this->cfg['httponly']);
	}

	public function del($key){
		return $this->set($key, null, -86400);
	}

	public function clear(){
		$len = strlen($this->cfg['prefix']);
		foreach($_COOKIE as $k => $v){
			$key = substr($k, $len);
			$this->set($key, null, -86400);
		}
	}

	private function enc($val){
		$md5 = md5(SALT);
		$num = ceil(strlen($val) / 32);
		$key = str_repeat($md5, $num);

		$val = base64_encode("$val" ^ $key);
		return chr(8) . $val;
	}

	private function dec($val){
		$val = base64_decode($val);
		$md5 = md5(SALT);
		$num = ceil(strlen($val) / 32);
		$key = str_repeat($md5, $num);

		return $val ^ $key;
	}
}