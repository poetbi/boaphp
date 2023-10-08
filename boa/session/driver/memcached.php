<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.session.driver.memcached.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\session\driver;

class memcached{
	private $cfg = [
		'server' => ['127.0.0.1', 11211]
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
			ini_set('memcached.sess_consistent_hash', 'On');
		}else{
			$save_path = $this->server($this->cfg['server']);
		}

		ini_set('session.save_handler', 'memcached');
		ini_set('session.save_path', $save_path);
		session_start();
    }

	private function server($v){
		$port = isset($v[1]) ? $v[1] : 11211;
		$server = $v[0] .':'. $port;
		if(isset($v[2])){
			$server .= ':'. $v[2];
		}
		return $server;
	}
}
?>