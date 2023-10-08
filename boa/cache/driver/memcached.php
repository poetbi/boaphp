<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.cache.driver.memcached.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\cache\driver;

use boa\msg;

class memcached{
	private $cfg = [
		'compress' => false,
        'expire' => 0,
        'prefix' => '',
		'persist' => 'persist_id',
		'timeout' => 0,
        'user' => '',
        'pass' => '',
		'server' => ['127.0.0.1', 11211, 1],
        'option' => []
    ];

	public function __construct($cfg){
		if(!extension_loaded('memcached')){
			msg::set('boa.error.41', 'Memcached');
		}

        if($cfg){
			$this->cfg = array_merge($this->cfg, $cfg);
		}

		if($this->cfg['expire'] > 2592000){
			$this->cfg['expire'] = 2592000;
		}

        if($this->cfg['persist']){
			$this->obj = new \Memcached($this->cfg['persist']);
		}else{
			$this->obj = new \Memcached();
		}

        if($this->cfg['compress']){
			$this->cfg['option'][\Memcached::OPT_COMPRESSION] = true;
		}
        if($this->cfg['timeout'] > 0){
			$this->cfg['option'][\Memcached::OPT_CONNECT_TIMEOUT] = $this->cfg['timeout'] * 1000;
		}
		if($this->cfg['option']){
            $this->obj->setOptions($this->cfg['option']);
        }

		if(is_array($this->cfg['server'][0])){
			foreach($this->cfg['server'] as $v){
				$this->server($v);
			}
		}else{
			$this->server($this->cfg['server']);
		}

		if($this->cfg['user'] != ''){
            $this->obj->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
            $this->obj->setSaslAuthData($this->cfg['user'], $this->cfg['pass']);
        }
	}

	public function get($name){
		return $this->obj->get($this->cfg['prefix'] . $name);
	}

	public function set($name, $val, $ttl = 0){
		if($ttl > 0) $ttl = time() + $ttl;
		return $this->obj->set($this->cfg['prefix'] . $name, $val, $ttl);
	}

	public function del($name){
		return $this->obj->delete($this->cfg['prefix'] . $name);
	}

	public function clear(){
		$this->obj->flush();
	}

	private function server($v){
		$port = isset($v[1]) ? $v[1] : 11211;
		$weight = isset($v[2]) ? $v[2] : 1;
		$this->obj->addServer($v[0], $port, $weight);
	}
}
?>