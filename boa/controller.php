<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.controller.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class controller{
	protected $request;
	protected $view;
	protected $common;
	
	public function __construct(){
		$this->request = boa::request();
		$this->view = boa::view();
		$this->common = boa::lib('common');
	}

	public function __get($k){
        return $this->request->v($k);
    }

	public function __set($k, $v){
        $this->request->$k = $v;
    }
	
	public function __call($method, $args){
		if(defined('DEBUG') && DEBUG){
			msg::set('boa.error.4', $method);
		}else{
			$this->view->lost();
		}
    }
}
?>