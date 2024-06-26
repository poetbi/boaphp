<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.log.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class log{
	private $cfg = [
		'enable' => false,
		'driver' => 'file',
		'type' => 'error,php_exception,php_error' //info,error,header,php_warning,php_error,php_deprecated,php_strict,php_exception
	];
	private $obj;
	private $log = [];

	public function __construct($cfg = []){
		if($cfg && !$cfg['enable']) return false;

		foreach($cfg as $k => $v){
			if(array_key_exists($k, $this->cfg)){
				$this->cfg[$k] = $v;
			}
		}

		$this->cfg['type'] = ','. str_replace(' ', '', $this->cfg['type']) .',';

		$driver = '\\boa\\log\\driver\\'. $this->cfg['driver'];
		$this->obj = new $driver($cfg);
	}

	public function cfg($k = null, $v = null){
		if(array_key_exists($k, $this->cfg)){
			if($v !== null){
				if($k == 'type'){
					$v = ','. str_replace(' ', '', $v) .',';
				}
				$this->cfg[$k] = $v;
			}
		}

		return $this->obj->cfg($k, $v);
	}

	public function set($type, $msg){
		if($this->cfg['enable']){
			if(strpos($this->cfg['type'], ",$type,") === false){
				return false;
			}

			if(!is_scalar($msg)){
				$msg = print_r($msg, true);
			}

			$this->log[] = [
				'time' => microtime(true),
				'type' => $type,
				'msg' => $msg
			];
		}
	}

	public function save(){
		if($this->cfg['enable']){
			if($this->log || strpos($this->cfg['type'], 'info') !== false){
				$arr = boa::info();
				$info['time'] = date('Y-m-d H:i:s', $arr['time_start']);
				$info['from'] = $_SERVER['REMOTE_ADDR'] .':'. $_SERVER['REMOTE_PORT'];
				$info['type'] = $_SERVER['REQUEST_METHOD'];
				$info['uri']  = $_SERVER['REQUEST_URI'];
				$info['use_mem'] = round(($arr['mem_end'] - $arr['mem_start']) / 1024, 2);
				$info['use_time'] = round($arr['time_end'] - $arr['time_start'], 4);

				if(PHP_SAPI != 'cli'){
					if(strpos($this->cfg['type'], 'header') !== false){
						array_unshift($this->log, [
							'time' => $arr['time_start'],
							'type' => 'header',
							'msg' => getallheaders()
						]);
					}
				}

				$this->obj->save($info, $this->log);
			}
		}
		$this->log = [];
	}
}
?>