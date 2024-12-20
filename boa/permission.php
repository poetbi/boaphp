<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.permission.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class permission extends base{
	protected $cfg = [
		'cacher' => 'permission',
		'mode' => 'a'
	];
	private $perms = [];
	private $perm = null;

	public function validate($group = '', $mode = null){
		$res = $this->check($group, $mode);
		if(!$res){
			msg::set('boa.error.21', $this->perm);
		}
	}

	public function check($group = '', $mode = null){
		$this->perm_code();
		if(!$mode) $mode = $this->cfg['mode'];
		$this->perms = boa::cache()->xget($this->cfg['cacher'], ['group' => $group]);

		$res = false;
		switch($mode){
			case 'a' :
				if($this->check_allow() == true) $res = true;
				break;

			case 'd' :
				if($this->check_deny() != true) $res = true;
				break;

			case 'ad' :
				if($this->check_allow() == true) $res = true;
				if($res == true && $this->check_deny() == true) $res = false;
				break;

			default :
				if($this->check_deny() != true) $res = true;
				if($res == false && $this->check_allow() == true) $res = true;
		}
		return $res;
	}

	private function check_allow(){
		if($this->perms['allow']){
			foreach($this->perms['allow'] as $v){
				if($this->match($v)) return true;
			}
		}
	}
	
	private function check_deny(){
		if($this->perms['deny']){
			foreach($this->perms['deny'] as $v){
				if($this->match($v)) return true;
			}
		}
	}
	
	private function match($v){
		$reg = preg_quote($v);
		$reg = str_replace('\*', '(\w+)', $reg);
		return preg_match("/^$reg$/", $this->perm);
	}
	
	private function perm_code(){
		if(!$this->perm){
			$mod = boa::env('mod');
			$con = boa::env('con');
			$act = boa::env('act');
			$this->perm = "$mod.$con.$act";
		}
		return $this->perm;
	}
}
?>
