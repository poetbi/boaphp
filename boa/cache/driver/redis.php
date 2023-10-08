<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.cache.driver.redis.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\cache\driver;

use boa\msg;

class redis{
	private $cfg = [
		'server' => ['127.0.0.1', 6379, 0],
        'expire' => 0,
        'prefix' => '',
		'persist' => true,
		'timeout' => 0,
        'option' => []
    ];

	public function __construct($cfg){
		if(!extension_loaded('redis')){
			msg::set('boa.error.41', 'Redis');
		}

        if($cfg){
			$this->cfg = array_merge($this->cfg, $cfg);
		}

        $this->obj = new \Redis();

        $connect = $this->cfg['persist'] ? 'connect' : 'pconnect';
		$host = $this->cfg['server'][0];
		$port = isset($this->cfg['server'][1]) ? $this->cfg['server'][1] : 6379;
		$select = isset($this->cfg['server'][2]) ? $this->cfg['server'][2] : 0;
		$auth = isset($this->cfg['server'][3]) ? $this->cfg['server'][3] : null;

		if($port == 0){
            $this->obj->$connect($host);
        }else{
            $this->obj->$connect($host, $port, $this->cfg['timeout']);
        }

        if(isset($auth)){
            $res = $this->obj->auth($auth);
			if(!$res){
				msg::set('boa.error.42');
			}
        }

		if($this->cfg['prefix'] != ''){
			$this->cfg['option'][\Redis::OPT_PREFIX] = $this->cfg['prefix'];
		}
		$this->cfg['option'][\Redis::OPT_SERIALIZER] = \Redis::SERIALIZER_NONE;
		if($this->cfg['option']){
			foreach($this->cfg['option'] as $k => $v){
				$this->obj->setOption($k, $v);
			}
        }

        if($select != 0){
            $this->obj->select($select);
        }
	}

	public function get($name){
		$res = $this->obj->get($name);
		if(substr($res, 0, 1) === chr(8)){
			$res = unserialize(substr($res, 1));
		}
		return $res;
	}

	public function set($name, $val, $ttl = 0){
		if(!is_scalar($val)){
			$val = chr(8) . serialize($val);
		}
		if($ttl > 0){
			return $this->obj->setex($name, $ttl, $val);
		}else{
			return $this->obj->set($name, $val);
		}
	}

	public function del($name){
		return $this->obj->del($name);
	}

	public function clear(){
		$this->obj->flushDb();
	}
}
?>