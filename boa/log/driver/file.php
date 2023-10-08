<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.log.driver.file.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\log\driver;

use boa\boa;
use boa\log\driver;

class file extends driver{
	protected $cfg = [
		'timeline' => false,
		'file' => '%Y%m/%d.log'
	];

	public function __construct($cfg){
		parent::__construct($cfg);

		$this->cfg['file'] = BS_VAR .'log/'. $this->format($this->cfg['file']);
	}

	public function save($info, $log){
		$str  = str_repeat('=', 20) .' Time:'. $info['use_time'] .'s  Memory:'. $info['use_mem'] .'kb '. str_repeat('=', 20) ."\r\n";
		$str .= '['. $info['time'] .']'. $info['from'] .' '. $info['type'] .' '. $info['uri'] ."\r\n";

		$timeline = '';
		foreach($log as $v){
			$type = $v['type'];
			$msg = $v['msg'];

			if(!is_scalar($msg)){
				$msg = rtrim(print_r($msg, true));
			}

			if($this->cfg['timeline']){
				$timeline = $this->timeline($v['time']);
			}

			$str .= $timeline ."[$type] $msg\r\n";
		}

		boa::file()->write($this->cfg['file'], "$str\r\n", true);
	}

	private function format($file){
		preg_match_all('/\%([a-zA-Z])/', $file, $arr);
		$str = implode('|', $arr[1]);
		$val = explode('|', date($str, time()));
		foreach($arr[1] as $k => $v){
			$file = str_replace("%$v", $val[$k], $file);
		}

		if(PHP_SAPI == 'cli'){
			$file = preg_replace('/\.log$/i', '.cli.log', $file);
		}

		return $file;
	}
}
?>