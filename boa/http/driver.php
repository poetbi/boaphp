<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.http.driver.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\http;

use boa\base;

class driver extends base{
	protected $cfg = [];
	protected $result = [];

	public function get_posttype(){
		return $this->cfg['posttype'];
	}
	
	public function get_header(){
		return $this->result['head'];
	}
	
	public function get_body(){
		return $this->result['body'];
	}
	
	public function get_status(){
		return $this->result['code'];
	}
	
	public function get_error(){
		return $this->result['msg'];
	}
}
?>