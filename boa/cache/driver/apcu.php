<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.cache.driver.apcu.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\cache\driver;

use boa\msg;

class apcu{
	private $cfg = [
        'expire' => 0,
        'prefix' => ''
    ];
	
    public function __construct($cfg){
        if(!function_exists('apcu_fetch')){
            msg::set('boa.error.41', 'APCu');
        }
		
        if($cfg){
			$this->cfg = array_merge($this->cfg, $cfg);
		}
    }

	public function get($name){
		return apcu_fetch($this->cfg['prefix'] . $name);
	}

	public function set($name, $val, $ttl = 0){
		return apcu_store($this->cfg['prefix'] . $name, $val, $ttl);
	}

	public function del($name){
		return apcu_delete($this->cfg['prefix'] . $name);
	}

	public function clear(){
		apcu_clear_cache();
	}
}
?>