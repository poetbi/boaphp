<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.cache.driver.xcache.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\cache\driver;

use boa\msg;

class xcache{
	private $cfg = [
        'expire' => 0,
        'prefix' => ''
    ];
	
    public function __construct($cfg){
        if(!function_exists('xcache_get')){
            msg::set('boa.error.41', 'Xcache');
        }

        if($cfg){
			$this->cfg = array_merge($this->cfg, $cfg);
		}
    }

	public function get($name){
		$res = xcache_get($this->cfg['prefix'] . $name);
		if(substr($res, 0, 1) === chr(8)){
			$res = unserialize(substr($res, 1));
		}
		if($res === null){
			$res = false;
		}
		return $res;
	}

	public function set($name, $val, $ttl = 0){
		if(!is_scalar($val)){
			$val = chr(8) . serialize($val);
		}
		return xcache_set($this->cfg['prefix'] . $name, $val, $ttl);
	}

	public function del($name){
		return xcache_unset($this->cfg['prefix'] . $name);
	}

	public function clear(){
		xcache_unset_by_prefix($this->cfg['prefix']);
	}
}
?>