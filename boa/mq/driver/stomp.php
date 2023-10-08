<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.mq.driver.stomp.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\mq\driver;

use boa\boa;
use boa\msg;
use boa\base;

class stomp extends base{
	protected $cfg = [
        'broker' => 'tcp://127.0.0.1:61613', //ssl://...
        'username' => null,
        'password' => null,
		'headers' => [], //'client-id' => ...
		'timeout' => 0, //s
		'queue' => '/queue/boa'
    ];
	private $headers = [];
	private $obj;

	public function __construct($cfg){
        parent::__construct($cfg);

		try{
			$this->obj = new \Stomp($this->cfg['broker'], $this->cfg['username'], $this->cfg['password'], $this->cfg['headers']);
		}catch(\StompException $e){
			msg::set('boa.error.131', '[stomp]'. $e->getMessage());
		}

		if($this->cfg['timeout'] > 0){
			$this->obj->setReadTimeout($this->cfg['timeout']);
		}
	}

	public function head(){
		return $this->headers;
	}

	public function session_id(){
		return $this->obj->getSessionId();
	}

	public function publish($msg){
		return $this->obj->send($this->cfg['queue'], $msg);
	}

	public function subscribe($queue = null){
		if(!$queue){
			$queue = $this->cfg['queue'];
		}
		return $this->obj->subscribe($queue);
	}

	public function has(){
		return $this->obj->hasFrame();
	}

	public function read(){
		$res = $this->obj->readFrame();

		$error = $this->obj->error();
		if($error){
			boa::log()->set('error', "[mq][stomp]$error");
			return false;
		}

		$this->headers = $res->headers;
		return $res->body;
	}

	public function ack(){
		return $this->obj->ack($this->headers['message-id']);
	}

	public function unsubscribe($queue = null){
		if(!$queue){
			$queue = $this->cfg['queue'];
		}
		return $this->obj->unsubscribe($queue);
	}
}
?>