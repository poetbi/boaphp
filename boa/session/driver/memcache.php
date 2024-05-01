<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.session.driver.memcache.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\session\driver;

class memcache{
	private $cfg = [
		'server' => ['localhost', 11211],
		'option' => []
    ];

	public function __construct($cfg){
        if($cfg){
			$this->cfg = array_merge($this->cfg, $cfg);
		}
		
		if(is_array($this->cfg['server'][0])){
			$save_path = '';
			foreach($this->cfg['server'] as $v){
				$save_path .= ','. $this->server($v);
			}
			$save_path = substr($save_path, 1);
			ini_set('memcache.hash_strategy', 'consistent');
		}else{
			$save_path = $this->server($this->cfg['server']);
		}
		
		ini_set('session.save_handler', 'memcache');
		ini_set('session.save_path', $save_path);
		session_start();
    }

	private function server($v){
		$port = isset($v[1]) ? $v[1] : 11211;
		if($port == 0){
			$host = 'unix://'. $v[0];
		}else{
			$host = 'tcp://'. $v[0];
		}

		if(isset($v[2])){
			$this->cfg['option']['weight'] = $v[2];
		}else{
			unset($this->cfg['option']['weight']);
		}

		if($this->cfg['option']){
			$param = '?'. http_build_query($this->cfg['option']);
		}

		$server = $host .':'. $port . $param;
		return $server;
	}
}
?>