<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.json.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class json extends base{
	protected $cfg = [
		'unicode' => true,
		'object' => false,
		'array' => true
	];

	public function encode($val, $type = null){
		$option = 0;
		if($this->cfg['unicode']){
			$option = $option | JSON_UNESCAPED_UNICODE;
		}
		if($type === null) $type = $this->cfg['object'];
		if($type){
			$option = $option | JSON_FORCE_OBJECT;
		}
		
		$res = json_encode($val, $option);

		if($res === false){
			if($errno = json_last_error()){
				$error = boa::getkey($errno, 0, 'json', 'JSON_ERROR_');
				msg::set('boa.error.71', "[$error]". json_last_error_msg());
			}
		}

		return $res;
	}

	public function decode($val, $type = null){
		if($val === null) return;

		if($type === null) $type = $this->cfg['array'];
		$res = json_decode($val, $type);

		if($res === null){
			if($errno = json_last_error()){
				$error = boa::getkey($errno, 0, 'json', 'JSON_ERROR_');
				msg::set('boa.error.72', "[$error]". json_last_error_msg());
			}
		}

		return $res;
	}
}
?>