<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.router.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class router{
	private $cfg = [
		'enable' => false,
		'type' => 0, //0=dynamic 1=pathinfo 2=rewrite
		'url_word' => '([a-z0-9\-]+)',
		'param_val' => '([^\/]*?)',
		'force' => false,
		'mod' => 'm',
		'con' => 'c',
		'act' => 'a',
		'default' => 'index',
		'delimiter' => '-'
	];
	private $env, $url;

	public function __construct($cfg = []){
        if($cfg){
			$this->cfg = array_merge($this->cfg, $cfg);
		}
	}

	public function env(){
		if(!$this->env){
			$this->env = new \boa\router\env($this->cfg);
		}
		return $this->env->get();
	}

	public function url($act, $param = [], $method = null, $scheme = null){
		if(!$this->url){
			$this->url = new \boa\router\url($this->cfg);
		}
		return $this->url->get($act, $param, $method, $scheme);
	}

	public function make($act, $args = []){
		$arr = [
			'REQUEST_URI' => '',
			'QUERY_STRING' => '',
			'PATH_INFO' => '',
		];
		$acts = explode('.', $act);
		$max = count($acts);

		$arr['QUERY_STRING'] = $this->cfg['mod'] .'='. $acts[0];
		$arr['PATH_INFO']    = '/'. $acts[0] .'/';
		if($max > 1){
			$arr['QUERY_STRING'] .= '&'. $this->cfg['con'] .'='. $acts[1];
			$arr['PATH_INFO']    .= $acts[1] .'/';
			if($max > 2){
				$arr['QUERY_STRING'] .= '&'. $this->cfg['act'] .'='. $acts[2];
				$arr['PATH_INFO']    .= $acts[2] .'/';
			}
		}

		$arr['REQUEST_URI'] = $arr['PATH_INFO'];

		$var = [];
		if($args){
			foreach($args as $i => $arg){
				if(strpos($arg, '=') > 0){
					list($k, $v) = explode('=', $arg);
					$var[$k] = $v;
				}else{
					$var[$i] = $arg;
				}
			}

			$str = http_build_query($var);
			if($this->cfg['type'] > 0){
				$arr['QUERY_STRING'] = $str;
			}else{
				$arr['QUERY_STRING'] .= '&' . $str;
			}
			foreach($var as $k => $v){
				$arr['PATH_INFO'] .= $k .$this->cfg['delimiter']. urlencode($v) .'/';
			}
			$arr['REQUEST_URI'] .= '?' . $str;
		}

		return $arr;
	}
}
?>