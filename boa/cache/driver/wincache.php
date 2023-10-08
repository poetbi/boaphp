<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.cache.driver.wincache.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\cache\driver;

use boa\msg;

class wincache{
	private $cfg = [
        'expire' => 0,
        'prefix' => ''
    ];
	
    public function __construct($cfg){
        if(!function_exists('wincache_ucache_get')){
            msg::set('boa.error.41', 'WinCache');
        }

        if($cfg){
			$this->cfg = array_merge($this->cfg, $cfg);
		}
    }

	public function get($name){
		$res = wincache_ucache_get($this->cfg['prefix'] . $name);
		if(substr($res, 0, 1) === chr(8)){
			$res = unserialize(substr($res, 1));
		}
		return $res;
	}

	public function set($name, $val, $ttl = 0){
		if(!is_scalar($val)){
			$val = chr(8) . serialize($val);
		}
		return wincache_ucache_set($this->cfg['prefix'] . $name, $val, $ttl);
	}

	public function del($name){
		return wincache_ucache_delete($this->cfg['prefix'] . $name);
	}

	public function clear(){
		wincache_ucache_clear();
	}
}
?>