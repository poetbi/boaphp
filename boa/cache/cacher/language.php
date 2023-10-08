<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.cache.cacher.language.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\cache\cacher;

use boa\boa;
use boa\msg;
use boa\cache\cacher;

class language implements cacher{
	private $mod;
	private $file;
	private $lng;

	public function __construct($args){
		$this->mod = $args['mod'];
		$this->file = $args['file'];
		$this->lng = $args['lng'];
	}

	public function get(){
		$res = [];

		if($this->mod == 'boa'){
			$path = BS_BOA . 'language/';
		}else{
			$path = BS_MOD . $this->mod .'/language/';
		}
		$file = $path . $this->lng .'/'. $this->file .'.php';
		if(file_exists($file)){
			$res = include($file);
		}

		return $res;
	}
}
?>