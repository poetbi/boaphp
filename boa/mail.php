<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.mail.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class mail{
	private $obj;

    public function __construct($cfg = []){
 		if(!array_key_exists('driver', $cfg)) $cfg['driver'] = 'smtp';

		$driver = '\\boa\\mail\\driver\\'. $cfg['driver'];
		$this->obj = new $driver($cfg);
    }

	public function cfg($k = null, $v = null){
		return $this->obj->cfg($k, $v);
	}

	public function anti_spam($client_ip = '', $proxy_server = '', $user_agent = ''){
		$header = [];

		if($_SERVER){
			if(!$client_ip){
				if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)){
					$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
				}elseif(array_key_exists('HTTP_CLIENT_IP', $_SERVER)){
					$client_ip = $_SERVER['HTTP_CLIENT_IP'];
				}elseif(array_key_exists('HTTP_FROM', $_SERVER)){
					$client_ip = $_SERVER['HTTP_FROM'];
				}elseif(array_key_exists('REMOTE_ADDR', $_SERVER)){
					$client_ip = $_SERVER['REMOTE_ADDR'];
				}
			}

			if(!$proxy_server){
				if(array_key_exists('REMOTE_ADDR', $_SERVER) && $client_ip != $_SERVER['REMOTE_ADDR']){
					$proxy_server = $_SERVER['REMOTE_ADDR'];
				}
			}

			if(!$user_agent){
				if(array_key_exists('HTTP_USER_AGENT', $_SERVER)){
					$user_agent = $_SERVER['HTTP_USER_AGENT'];
				}else{
					$user_agent = "BOA Mailer";
				}
			}
		}

		if($client_ip){
			$header['X-HTTP-Posting-Host'] = $client_ip;
		}

		if($proxy_server){
			$header['X-HTTP-Proxy-Server'] = $proxy_server;
		}

		if($user_agent){
			$header['X-HTTP-Posting-UserAgent'] = $user_agent;
		}

		$this->obj->anti_spam($header);
		return $this;
	}

	public function send($subject, $message, $to = null){
		$from = $this->obj->cfg('from');
		if(!$from){
			$from = $this->obj->cfg('smtp.user');
			$this->obj->cfg('from', $from);
		}
		list($addr, $name) = explode(' ', $from, 2);
		$this->obj->cfg('from_addr', $addr);
		$this->obj->cfg('from_name', trim($name));
		return $this->obj->send($subject, $message, $to);
	}
}
?>