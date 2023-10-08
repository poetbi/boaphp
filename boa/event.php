<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.event.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class event{
	private $cfg = [
		'auto' => false,
		'listener' => [
			'init' 			=> '\\boa\\event\\listener\\init',
			'module' 		=> '\\boa\\event\\listener\\module',
			'controller' 	=> '\\boa\\event\\listener\\controller',
			'action' 		=> '\\boa\\event\\listener\\action'
		]
	];

    public function __construct($cfg = []){
		if(array_key_exists('auto', $cfg)){
			$this->cfg['auto'] = $cfg['auto'];
		}
		if($cfg['listener']){
			$this->cfg['listener'] = array_merge($this->cfg['listener'], $cfg['listener']);
		}
    }

	public function have($key){
		return $this->cfg['listener'][$key] ? true : false;
	}

	public function register($key, $listener){
		$this->cfg['listener'][$key] = $listener;
	}

	public function remove($key){
		unset($this->cfg['listener'][$key]);
	}

	public function trigger($key, $args = []){
		$listener = $this->cfg['listener'][$key];
		if(!$this->cfg['auto']){
			if(!$listener){
				return false;
			}
		}else{
			if(!$listener){
				$mod = boa::env('mod');
				$file = BS_MOD ."$mod/listener/$key.php";
				if(file_exists($file)){
					$listener = "\\mod\\$mod\\listener\\$key";
				}
			}
		}

		$res = true;
		if($listener){
			$obj = new $listener($args);
			$res = $obj->get();
		}
		return $res;
	}
}
?>