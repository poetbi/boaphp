<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.log.driver.net.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\log\driver;

use boa\boa;
use boa\log\driver;

class net extends driver{
	protected $cfg = [
		'timeline' => false,
		'host' => 'http://127.0.0.1:8888'
	];
	private $obj;

	public function __construct($cfg){
		parent::__construct($cfg);

		$this->obj = boa::http($cfg);
	}

	public function save($info, $log){
		$timeline = '';
		foreach($log as $k => $v){
			$type = $v['type'];
			$msg = $v['msg'];

			if(!is_scalar($msg)){
				$msg = rtrim(print_r($msg, true));
			}

			if($this->cfg['timeline']){
				$timeline = $this->timeline($v['time']);
			}

			$log[$k] = "$timeline [$type] $msg";
		}
		$info['log'] = $log;

		$this->obj->post($this->cfg['host'], $info);
	}
}
?>