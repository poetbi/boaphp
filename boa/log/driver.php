<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.log.driver.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\log;

use boa\boa;
use boa\base;

class driver extends base{
	protected $cfg = [];
	protected $time_start;

	public function __construct($cfg){
        if($cfg){
			$this->cfg = array_merge($this->cfg, $cfg);
		}
		$this->time_start = boa::info('time_start');
	}

	protected function timeline($time){
		$str = round($time - $this->time_start, 4);
		if($str <= 0){
			return '';
		}

		$str = str_repeat(' ', 6) . $str;
		$str = '['. substr($str, -7) .']';
		return $str;
	}
}
?>