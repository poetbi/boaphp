<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.validater.request.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\validater;

class request{
	private $method;

	public function is_get(){
		return $this->method() == 'GET';
	}

	public function is_post(){
		return $this->method() == 'POST';
	}

	public function is_head(){
		return $this->method() == 'HEAD';
	}

	public function is_options(){
		return $this->method() == 'OPTIONS';
	}

	public function is_put(){
		return $this->method() == 'PUT';
	}

	public function is_delete(){
		return $this->method() == 'DELETE';
	}

	public function is_cli(){
		$res = PHP_SAPI == 'cli' ? true : false;
		return $res;
	}

	public function is_cgi(){
		$res = PHP_SAPI == 'cgi' ? true : false;
		return $res;
	}

	public function is_ssl(){
		if($_SERVER['HTTPS']){
			return true;
		}else if($_SERVER['REQUEST_SCHEME'] == 'https'){
			return true;
		}else if($_SERVER['SERVER_PORT'] == 443){
			return true;
		}else if($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
			return true;
		}
		return false;
	}

	public function is_ajax($param = '_ajax'){
		if(
			$_GET[$param]
			 || $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'
		){
			return true;
		}else{
			return false;
		}
	}

	public function is_pjax($param = '_pjax'){
		if(
			$_GET[$param]
			 || isset($_SERVER['HTTP_X_PJAX'])
		){
			return true;
		}else{
			return false;
		}
	}

	public function is_mobile(){
		if(isset($_SERVER['HTTP_X_WAP_PROFILE'])){
			return true;
		}else if(
			$_SERVER['HTTP_VIA']
			 && stristr($_SERVER['HTTP_VIA'], 'wap')
		){
			return true;
		}else if($_SERVER['HTTP_USER_AGENT']){
			$agent = 'android|iphone|ipod|mobile|mobi|wap|phone|pocket|huawei|samsung|nokia|blackberry|symbian|motorola|opera |hp( |\-)|htc( |_|\-)|windows ce|xda( |_)|palm|kindle|midp|mmp|portalmmm|sqh|spv|treo|sonyericsson|vodafone';
			$res = preg_match("/($agent)/i", $_SERVER['HTTP_USER_AGENT']);
			if($res){
				return true;
			}
		}
		return false;
	}

	private function method(){
		if(!$this->method){
			if($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']){
				$method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
			}else{
				$method = $_SERVER['REQUEST_METHOD'];
			}
			$this->method = strtoupper($method);
		}
		return $this->method;
	}
}
?>