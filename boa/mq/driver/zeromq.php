<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.mq.driver.zeromq.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\mq\driver;

use boa\boa;
use boa\msg;
use boa\base;

class zeromq extends base{
	protected $cfg = [
        'broker' => 'tcp://127.0.0.1:5555', //inproc, ipc, tcp, pgm, epgm
        'persist_id' => null,
		'queue' => 'boa'
    ];
	private $obj = [];

	public function __construct($cfg){
        parent::__construct($cfg);

		if(!extension_loaded('zmq')){
			msg::set('boa.error.6', 'ZMQ');
		}
	}

	public function publish($msg){
		if($this->cfg['queue']){
			$msg = $this->cfg['queue'] .' '. $msg;
		}
		try{
			$this->obj(\ZMQ::SOCKET_PUB)->send($msg);
			return true;
		}catch(\Exception $e){
			boa::log()->set('error', '[mq][zeromq]'. $e->getMessage());
			return false;
		}
	}

	public function subscribe($queue = null){
		if(!$queue){
			$queue = $this->cfg['queue'];
		}
		try{
			$obj = $this->obj(\ZMQ::SOCKET_SUB);
			if($queue){
				$obj->setSockOpt(\ZMQ::SOCKOPT_SUBSCRIBE, $queue);
			}
			return true;
		}catch(\Exception $e){
			boa::log()->set('error', '[mq][zeromq]'. $e->getMessage());
			return false;
		}
	}

	public function read(){
		try{
			$msg = $this->obj(\ZMQ::SOCKET_SUB)->recv();
			if($this->cfg['queue']){
				$arr = explode(' ', $msg, 2);
				$msg = $arr[1];
			}
			return $msg;
		}catch(\Exception $e){
			boa::log()->set('error', '[mq][zeromq]'. $e->getMessage());
			return false;
		}
	}

	public function ack(){
		return true;
	}

	public function unsubscribe($queue = null){
		$this->obj(\ZMQ::SOCKET_SUB)->disconnect($this->cfg['broker']);
		return true;
	}

	private function obj($type){
		if(!$this->obj[$type]){
			$context = new \ZMQContext();
			$obj = new \ZMQSocket($context, $type, $this->cfg['persist_id']);
			if($type == \ZMQ::SOCKET_PUB){
				$obj->bind($this->cfg['broker']);
			}else{
				$obj->connect($this->cfg['broker']);
			}
			$this->obj[$type] = $obj;
		}
		return $this->obj[$type];
	}
}
?>