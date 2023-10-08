<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.security.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class security extends base{
	protected $cfg = [
		'xss_tags' => ['script', 'base'],
		'xss_events' => ['on*'],
		'csrf_key' => 'CSRF-TOKEN',
		'csrf_type' => 1, //0=close 1=client 2=server 3=both
		'csrf_expire' => 0
	];
	private $obj;
	
	function xss($str){
		$tag = implode('|', $this->cfg['xss_tags']);
		$str = preg_replace("/<($tag)[^>]*>([\s\S]*?)<\/($tag)>/i", '', $str);
		$str = preg_replace("/<($tag)[^>]*>/i", '', $str);

		$event = implode('|', $this->cfg['xss_events']);
		$event = str_replace('*', '[a-z]+', $event);
		$str = preg_replace("/<([^>]+?)\s+($event)\s*=\s*(\"|')([\s\S]+?)(\"|')(\s+|>)/i", '<$1 $6', $str);

		return $str;
	}
	
	public function create(){
		$token = null;
		
		if($this->cfg['csrf_type'] > 0){
			$token = $this->generate($_SERVER['REQUEST_TIME']);

			if($this->cfg['csrf_type'] == 1){
				$this->obj()->set($this->cfg['csrf_key'], $token, 0, true);
			}else if($this->cfg['csrf_type'] > 1){
				$this->obj()->set($this->cfg['csrf_key'], $token);
			}
		}

		return $token;
	}

	public function check(){
		if($this->cfg['csrf_type'] <= 0 || PHP_SAPI == 'cli'){
			return true;
		}

		$res = false;
		$token = $this->obj()->get($this->cfg['csrf_key']);

		if($token){
			$time = substr($token, 0, 10);
			$diff = time() - $time;
			if($this->cfg['csrf_expire'] == 0 || $diff <= $this->cfg['csrf_expire']){
				$_token = $this->generate($time);
				if($_token == $token){
					$res = true;
				}

				if($this->cfg['csrf_type'] == 3){
					if($_SERVER['REQUEST_METHOD'] == 'POST'){
						$_token = $_POST[$this->cfg['csrf_key']];
					}else{
						$_token = $_GET[$this->cfg['csrf_key']];
					}
					if(!$_token) $_token = $_SERVER['X-CSRF-TOKEN'];
					if($_token == $token){
						$res = true;
					}else{
						$res = false;
					}
				}
			}
		}

		$this->obj()->del($this->cfg['csrf_key']);
		return $res;
	}

	public function validate(){
		$res = $this->check();
		if(!$res){
			msg::set('boa.error.23', 'csrf');
		}
	}

	private function generate($time){
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$token = md5($agent . $time . SALT);
		$token = $time . substr($token, 10);
		return $token;
	}

	private function obj(){
		if(!$this->obj){
			if($this->cfg['csrf_type'] == 1){
				$this->obj = boa::cookie();
			}else if($this->cfg['csrf_type'] > 1){
				$this->obj = boa::session();
			}
		}
		return $this->obj;
	}
}
?>