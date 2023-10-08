<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.cache.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class cache{
	private $obj;
	private $key;
	private $ttl = 0;

	public function __construct($cfg = []){
		if(!$cfg['driver']){
			$cfg['driver'] = 'file';
		}

		if(array_key_exists('expire', $cfg)){
			$this->ttl = $cfg['expire'];
		}

		$driver = '\\boa\\cache\\driver\\'. $cfg['driver'];
		$this->obj = new $driver($cfg);
	}

	public function key(){
		return $this->key;
	}

	public function get($name){
		return $this->obj->get($name);
	}

	public function set($name, $val, $ttl = 0){
		if($ttl == 0) $ttl = $this->ttl;
		$res = $this->obj->set($name, $val, $ttl);
		if($res){
			$this->key = $name;
		}
		return $res;
	}

	public function xget($name, $args = [], $ttl = 0){
		$cname = $this->cname($name, $args);
		$res = $this->obj->get($cname);
		if($res === false){
			if($ttl == 0) $ttl = $this->ttl;
			$res = $this->xset($name, $cname, $args, $ttl);
		}
		return $res;
	}

	private function xset($name, $cname, $args, $ttl){
		$val = $this->create($name, $args)->get();
		$res = $this->obj->set($cname, $val, $ttl);
		if($res){
			$this->key = $cname;
			return $val;
		}
		return $res;
	}

	public function del($name){
		return $this->obj->del($name);
	}

	public function clear(){
		$this->obj->clear();
	}

	private function cname($name, $args){
		if($args){
			ksort($args);
			$key = json_encode($args);
			$key = abs(crc32($key));
			$cname = "$name-$key";
		}else{
			$cname = $name;
		}
		return $cname;
	}

	private function create($name, $args){
		$arr = explode('.', $name, 2);
		if(count($arr) > 1){
			$mod = $arr[0];
			$name = $arr[1];
			$cls = "\\mod\\$mod\\cacher\\$name";
		}else{
			$cls = "\\boa\\cache\\cacher\\$name";
		}
		return new $cls($args);
	}
}