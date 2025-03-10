<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.cache.driver.memcache.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\cache\driver;

use boa\msg;

class memcache{
	private $cfg = [
		'compress' => false,
        'expire' => 0,
        'prefix' => '',
		'server' => ['localhost', 11211, true, 1, 1]
    ];
	private $obj;

	public function __construct($cfg){
		if(!extension_loaded('memcache')){
			msg::set('boa.error.41', 'Memcache');
		}

        if($cfg){
			$this->cfg = array_merge($this->cfg, $cfg);
		}

		if($this->cfg['expire'] > 2592000){
			$this->cfg['expire'] = 2592000;
		}

		$this->obj = new \Memcache();

		if(is_array($this->cfg['server'][0])){
			foreach($this->cfg['server'] as $v){
				$this->server($v);
			}
		}else{
			$this->server($this->cfg['server']);
		}
	}

	public function get($name){
		$flag = $this->cfg['compress'] ? MEMCACHE_COMPRESSED : 0;
		return $this->obj->get($this->cfg['prefix'] . $name, $flag);
	}

	public function set($name, $val, $ttl = 0){
		if($ttl > 0) $ttl = time() + $ttl;
		$flag = $this->cfg['compress'] ? MEMCACHE_COMPRESSED : 0;
		return $this->obj->set($this->cfg['prefix'] . $name, $val, $flag, $ttl);
	}

	public function del($name){
		return $this->obj->delete($this->cfg['prefix'] . $name);
	}

	public function clear(){
		$this->obj->flush();
	}

	private function server($v){
		$port = isset($v[1]) ? $v[1] : 11211;
		$host = $port == 0 ? 'unix://'. $v[0] : $v[0];
		$persist = isset($v[2]) ? $v[2] : false;
		$weight = isset($v[3]) ? $v[3] : 1;
		$timeout = isset($v[4]) ? $v[4] : 1;
		$this->obj->addServer($host, $port, $persist, $weight, $timeout);
	}
}
?>