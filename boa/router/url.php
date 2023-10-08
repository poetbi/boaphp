<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.router.url.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\router;

use boa\boa;
use boa\msg;

class url{
	private $cfg = [];
	private $url = [];

	public function __construct($cfg){
		$this->cfg = $cfg;

		if($this->cfg['enable']){
			$url = boa::cache()->xget('url');
			$this->url = $url;
		}
	}

	public function get($act, $param = [], $method = null, $scheme = null){
		$act = $this->act($act);

		if($scheme){
			$scheme = strtolower($scheme);
		}

		if($method){
			$method = strtoupper($method);
		}

		$url = '';
		if($this->cfg['enable']){
			foreach($this->url as $k => $v){
				if($k != $act){
					if(strpos($k, '%') !== false){
						preg_match_all('/%(\d)/', $k, $keys);
						$k = preg_quote($k, '/');
						$k = preg_replace('/%\d/', $this->cfg['url_word'], $k);
						if(preg_match("/^$k$/", $act, $arr)){
							array_shift($arr);
							$arr = array_combine($keys[1], $arr);
						}else{
							continue;
						}
					}else{
						continue;
					}
				}

				foreach($v as $vv){
					if($method && $method != $vv['method']){
						continue;
					}

					if($scheme && $scheme != $vv['scheme']){
						continue;
					}

					$res = $this->compare_param($vv['param'], $param);
					if($res){
						$url_model = $vv['url'];
						if(substr($url_model, 0, 2) == '//'){
							$url_model = $_SERVER['REQUEST_SCHEME'] .':'. $url_model;
						}

						if($arr){
							foreach($arr as $i => $var){
								$url_model = str_replace("%$i", $var, $url_model);
							}
						}

						$vv['url'] = $url_model;
						$url = $this->generate_url($vv, $param);
						break;
					}
				}
			}
		}

		if(!$url){
			$url = $this->generate_url_other($act, $param);
		}
		return $url;
	}

	private function act($act){
		$num = 2 - substr_count($act, '.');
		if($num > 0){
			$act .= str_repeat('.'. $this->cfg['default'], $num);
		}
		return $act;
	}

	private function compare_param($param_reg, $param){
		foreach($param_reg as $k => $v){
			if(!preg_match("/^$v$/", $param[$k])){
				return false;
			}
		}
		return true;
	}

	private function generate_url($item, $param){
		$url = $item['url'];
		foreach($item['param'] as $k => $v){
			$val = urlencode($param[$k]);
			$url = str_replace('{'. $k .'}', $val, $url);
			unset($param[$k]);
		}

		$str = http_build_query($param);
		if($str){
			$url .= "?$str";
		}
		return $url;
	}

	private function generate_url_other($act, $param){
		if($this->cfg['type'] > 0){
			$file = $this->cfg['type'] == 1 ? 'index.php/' : '';
			$url = WWW . $file . str_replace('.', '/', $act) .'/';
			foreach($param as $k => $v){
				$url .= $k .$this->cfg['delimiter']. urlencode($v) .'/';
			}
		}else{
			$arr = explode('.', $act);
			$url = WWW .'?';
			$url .= $this->cfg['mod'] .'='. $arr[0] .'&'. $this->cfg['con'] .'='. $arr[1] .'&'. $this->cfg['act'] .'='. $arr[2];
			$url .= '&'. http_build_query($param);
		}
		return $url;
	}
}
?>