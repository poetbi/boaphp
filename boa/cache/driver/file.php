<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.cache.driver.file.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\cache\driver;

use boa\boa;
use boa\msg;

class file{
	private $cfg = [
        'path' => BS_VAR .'cache/',
        'expire' => 0,
        'prefix' => ''
    ];

	public function __construct($cfg){
        if($cfg){
			$this->cfg = array_merge($this->cfg, $cfg);
		}
	}

	public function get($name){
		$res = false;
		$file = $this->cfg['path']. $this->cfg['prefix'] ."$name.dat";
		if(file_exists($file) && filemtime($file) > time()){
			$res = file_get_contents($file);
		}

		if($res !== false){
			if(substr($res, 0, 1) === chr(8)){
				$res = unserialize(substr($res, 1));
			}
		}
		return $res;
	}

	public function set($name, $val, $ttl = 0){
		$file = $this->cfg['path']. $this->cfg['prefix'] ."$name.dat";
		if(!is_scalar($val)){
			$val = chr(8) . serialize($val);
		}
		$res = boa::file()->write($file, $val);
		if($ttl == 0) $ttl = 86400 * 3650;
		touch($file, time() + $ttl);
		return $res;
	}

	public function del($name){
		$fp = opendir($this->cfg['path']);
		if($fp){
			while(false !== ($v = readdir($fp))){
				if($v == '.' || $v == '..'){
					continue;
				}

				if(preg_match('/^'. $this->cfg['prefix'] . $name .'(\-|\.)/', $v)){
					unlink($this->cfg['path'] . $v);
				}
			}
			closedir($fp);
		}
		return true;
	}

	public function clear(){
		$fp = opendir($this->cfg['path']);
		if($fp){
			while(false !== ($v = readdir($fp))){
				if($v != '.' && $v != '..'){
					unlink($this->cfg['path'] . $v);
				}
			}
			closedir($fp);
		}
	}
}
?>