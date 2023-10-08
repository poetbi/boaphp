<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.session.driver.redis.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\session\driver;

class redis{
	private $cfg = [
		'server' => ['127.0.0.1', 6379],
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
		}else{
			$save_path = $this->server($this->cfg['server']);
		}
		
		ini_set('session.save_handler', 'redis');
		ini_set('session.save_path', $save_path);
		session_start();
    }

	private function server($v){
		$port = isset($v[1]) ? $v[1] : 6379;
		if($port == 0){
			$host = 'unix://'. $v[0];
		}else{
			$host = 'tcp://'. $v[0] .':'. $port;
		}

		if(isset($v[2])){
			$this->cfg['option']['weight'] = $v[2];
		}else{
			unset($this->cfg['option']['weight']);
		}

		if($this->cfg['option']){
			$param = '?'. http_build_query($this->cfg['option']);
		}

		return $host . $param;
	}
}
?>