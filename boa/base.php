<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.base.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class base{
	protected $cfg = [];

	public function __construct($cfg = []){
        if($cfg){
			$this->cfg = array_merge($this->cfg, $cfg);
		}
	}

	public function cfg($k = null, $v = null){
		switch(true){
			case $k === null && $v === null:
				return $this->cfg;

			case $v === null:
				$arr = explode('.', $k);
				$cfg = $this->cfg[$arr[0]];
				for($i = 1; $i < count($arr); $i++){
					$cfg = $cfg[$arr[$i]];
				}
				return $cfg;

			default:
				$arr = explode('.', $k);
				if(count($arr) > 1){
					$this->cfg[$arr[0]][$arr[1]] = $v;
				}else{
					$this->cfg[$k] = $v;
				}
				return $this;
		}
	}

	public function __get($k){
		return $this->cfg($k);
	}

	public function __set($k, $v){
		return $this->cfg($k, $v);
	}
}
?>