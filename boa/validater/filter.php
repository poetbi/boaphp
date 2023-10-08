<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.validater.filter.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\validater;

class filter{
	public function xss($v){
		return \boa\boa::security()->xss($v);
	}

	public function name($v){
		return preg_replace('/[^\w]+/', '', $v);
	}

	public function alpha($v){
		return preg_replace('/[^a-zA-Z]+/', '', $v);
	}

	public function digit($v){
		return preg_replace('/[^0-9]+/', '', $v);
	}

	public function chinese($v){
		return preg_replace('/[^\x{4e00}-\x{9fa5}]+/u', '', $v);
	}

	public function chinese_ex($v){
		return preg_replace('/[^\x{4e00}-\x{9fa5} a-zA-Z0-9]+/u', '', $v);
	}

	public function graph($v){
		return preg_replace('/[\s]+/', '', $v);
	}

	public function int($v){
		return preg_replace('/[^\-\d,]+$/', '', $v);
	}

	public function float($v){
		return preg_replace('/[^\-\d,\.]+$/', '', $v);
	}

	public function alnum($v){
		return preg_replace('/[^a-zA-Z0-9]+/', '', $v);
	}
}
?>