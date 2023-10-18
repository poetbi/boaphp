<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.cache.cacher.router.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\cache\cacher;

use boa\boa;
use boa\msg;
use boa\cache\cacher;

class router implements cacher{
	private $router = [];
	private $param_var = '/\{(\w+?)\}/';
	private $param_val = '([^\/]*?)';
	private $default = 'index';

	public function __construct($args){
		if(defined('ROUTER')){
			if(ROUTER && ROUTER['param_val']){
				$this->param_val = ROUTER['param_val'];
				$this->default = ROUTER['default'];
			}
		}

		$file = BS_WWW .'cfg/router.php';
		if(file_exists($file)){
			$this->router = include($file);
		}
	}

	public function get(){
		$res = [];

		foreach($this->router as $group => $rules){
			if(substr($group, 0, 2) != '//'){
				$group = ltrim($group, '/');
			}
			if(!$group || is_numeric($group)){
				$group = '';
			}

			if(is_string($rules['url']) && is_string($rules['act'])){
				$arr = $this->parse($rules);
				$res[$group][] = $arr;
			}else{
				foreach($rules as $rule){
					if(is_array($rule)){
						$arr = $this->parse($rule);
						$res[$group][] = $arr;
					}
				}
			}
		}

		return $res;
	}

	private function parse($v){
		$res = [];

		$num = preg_match_all($this->param_var, $v['url'], $arr);
		$url = trim($v['url'], '/');
		$url = preg_quote($url, '/') .'[\/]?';
		if($num){
			$var = [];
			foreach($arr[0] as $kk => $vv){
				$para = $arr[1][$kk];
				$para_reg = $v['param'][$para];
				if($para_reg){
					$re = $para_reg;
				}else{
					$re = $this->param_val;
				}

				$vv = preg_quote($vv, '/');
				$url = str_replace($vv, $re, $url);

				$var[] = $para;
			}

			$res['var'] = $var;
			$res['url'] = $url;
		}else{
			$res['url'] = $url;
		}

		$res['act'] = $this->act($v['act']);

		if($v['scheme']){
			$res['scheme'] = strtolower($v['scheme']);
		}

		if($v['method']){
			$res['method'] = strtoupper($v['method']);
		}

		return $res;
	}

	private function act($act){
		$num = 2 - substr_count($act, '.');
		if($num > 0){
			$act .= str_repeat('.'. $this->default, $num);
		}
		return $act;
	}
}
?>