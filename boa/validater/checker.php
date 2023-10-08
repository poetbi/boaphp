<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.validater.checker.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\validater;

class checker{
	public function required($v){
		$res = is_null($v) || $v === '' || $v === [];
		return $res ? 931 : 0;
	}

	public function is_ip($v){
		$res = filter_var($v, FILTER_VALIDATE_IP);
		return $res ? 0 : 932;
	}

	public function is_email($v){
		$flag = defined('FILTER_FLAG_EMAIL_UNICODE') ? FILTER_FLAG_EMAIL_UNICODE : null;
		$res = filter_var($v, FILTER_VALIDATE_EMAIL, $flag);
		return $res ? 0 : 933;
	}

	public function is_url($v){
		$res = filter_var($v, FILTER_VALIDATE_URL);
		return $res ? 0 : 934;
	}

	public function is_mobile_cn($v){
		$res = preg_match('/^(86|\(86\)|86\-)?1[3-9][\d]{9}$/', $v);
		return $res ? 0 : 935;
	}

	public function is_tel_cn($v){
		$res = preg_match('/^(86|\(86\)|86\-)?0[\d]{3}[\-]?[\d]{7,8}$/', $v);
		return $res ? 0 : 936;
	}

	public function is_idcard_cn($v){
		$v = trim($v);
		if(strlen($v) > 15){
			$res = preg_match('/^[1-9]\d{5}(19|20)[\d]{2}(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9|3[0-1])[\d]{3}[\dxX]$/', $v);
		}else{
			$res = preg_match('/^[1-9]\d{5}[\d]{2}(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9|3[0-1])[\d]{3}$/', $v);
		}
		return $res ? 0 : 937;
	}

	public function is_chinese($v){
		$res = preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $v);
		return $res ? 0 : 938;
	}

	public function is_chinese_ex($v){
		$res = preg_match('/^[\x{4e00}-\x{9fa5} a-zA-Z0-9]+$/u', $v);
		return $res ? 0 : 939;
	}

	public function is_number($v){
		$res = preg_match('/^[\-]?[\d,]+[\.\d]*$/', $v);
		return $res ? 0 : 940;
	}

	public function is_alnum($v){
		$res = ctype_alnum($v);
		return $res ? 0 : 941;
	}

	public function is_alpha($v){
		$res = ctype_alpha($v);
		return $res ? 0 : 942;
	}

	public function is_digit($v){
		$res = ctype_digit($v .'');
		return $res ? 0 : 943;
	}

	public function is_graph($v){
		$res = ctype_graph($v);
		return $res ? 0 : 944;
	}

	public function is_name($v){
		$res = preg_match('/^[\w]+$/', $v);
		return $res ? 0 : 945;
	}

	public function is_scalar($v){
		$res = is_scalar($v);
		return $res ? 0 : 946;
	}
}
?>