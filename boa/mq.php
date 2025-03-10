<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.mq.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class mq{
	private $obj;

	public function __construct($cfg = []){
		if(!array_key_exists('driver', $cfg)) $cfg['driver'] = 'stomp';

		$driver = '\\boa\\mq\\driver\\'. $cfg['driver'];
		$this->obj = new $driver($cfg);
	}

	public function cfg($k = null, $v = null){
		return $this->obj->cfg($k, $v);
	}

	public function publish($msg){
		if(!is_scalar($msg)){
			$msg = chr(8) . serialize($msg);
		}

		return $this->obj->publish($msg);
	}

	public function subscribe($queue = null){
		return $this->obj->subscribe($queue);
	}

	public function read(){
		$res = $this->obj->read();

		if(substr($res, 0, 1) === chr(8)){
			$res = unserialize(substr($res, 1));
		}

		return $res;
	}
	
	public function ack(){
		return $this->obj->ack();
	}

	public function unsubscribe($queue = null){
		return $this->obj->unsubscribe($queue);
	}

	public function obj(){
		return $this->obj;
    }
}