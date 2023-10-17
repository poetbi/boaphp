<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.validater.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class validater{
	private $field, $label, $value;
	private $type;
	private $temp = [];
	private $checker = null;
	private $filter = null;

	public function execute($key, $val, $rule){
		$this->field = $key;
		$this->value = $val;
		if($rule['label']) $this->label = $this->get_lng($rule['label']);

		msg::set_data([
			'field' => $this->field,
			'label' => $this->label
		]);
		foreach($rule as $k => $v){
			if($k && method_exists($this, $k)){
				$this->type = $k;
				$this->$k($v);
			}
		}
		msg::set_data();

		return $this->value;
	}

	private function chars($term){
		if(is_array($this->value)){
			foreach($this->value as $k => $v){
				$val = util::len($v);
				$this->_check($val, $term);
			}
		}else{
			$val = util::len($this->value);
			$this->_check($val, $term);
		}
	}

	private function value($term){
		if(is_array($this->value)){
			foreach($this->value as $k => $v){
				$this->_check($v, $term);
			}
		}else{
			$this->_check($this->value, $term);
		}
	}

	private function items($term){
		$val = is_array($this->value) ? count($this->value) : 0;
		$this->_check($val, $term);
	}

	private function equal($term){
		$value = $_POST[$term];
		if($this->value != $value){
			msg::set('boa.error.969', $this->label);
		}
	}

	private function check($term){
		$term = str_replace(' ', '', $term);
		$arr = explode('&', $term);
		foreach($arr as $v){
			if(method_exists($this->cls_checker(), $v)){
				if(is_array($this->value)){
					foreach($this->value as $v_k => $v_v){
						$res = $this->checker->$v($v_v);
						if($res){
							break;
						}
					}
				}else{
					$res = $this->checker->$v($this->value);
				}
				if($res){
					if(is_int($res)){
						if($res > 999){
							$mod = boa::env('mod');
							msg::set("$mod.error.$res", $this->label);
						}else{
							msg::set("boa.error.$res", $this->label);
						}
					}else{
						msg::set('boa.error.1', '['. $this->label .']'. $res);
					}
				}
			}else{
				msg::set('boa.error.900', $this->field .' > '. $this->type .' > '. $v);
			}
		}
	}

	private function filter($term){
		$term = str_replace(' ', '', $term);
		$arr = explode('&', $term);
		foreach($arr as $fun){
			if(!$fun){
				continue;
			}

			$res = null;
			if(method_exists($this->cls_filter(), $fun)){
				if(is_array($this->value)){
					foreach($this->value as $k => $v){
						$res[$k] = $this->filter->$fun($v);
					}
				}else{
					$res = $this->filter->$fun($this->value);
				}
			}else if(function_exists($fun)){
				if(is_array($this->value)){
					foreach($this->value as $k => $v){
						$res[$k] = $fun($v);
					}
				}else{
					$res = $fun($this->value);
				}
			}else{
				msg::set('boa.error.6', $fun .'()');
			}
			$this->value = $res;
		}
	}

	private function _check($val, $term){
		$result = false;
		$temp = [];
		$this->temp = [];

		$term = str_replace(' ', '', $term);
		$or = explode('|', $term);
		foreach($or as $k => $v){
			$temp[$k] = true;
			
			$and = explode('&', $v);
			foreach($and as $kk => $vv){
				$matched = preg_match('/^([!<>=]+)([\-]?\d+[\.\d]*)$/', $vv, $item);
				if($matched){
					$res = false;
					switch($item[1]){
						case '>=':
							$res = ($val >= $item[2]);
							$code = 1;
							break;

						case '<=':
							$res = ($val <= $item[2]);
							$code = 2;
							break;

						case '>':
							$res = ($val > $item[2]);
							$code = 3;
							break;

						case '<':
							$res = ($val < $item[2]);
							$code = 4;
							break;

						case '==':
						case '=':
							$res = ($val == $item[2]);
							$code = 5;
							break;

						case '!=':
						case '<>':
							$res = ($val != $item[2]);
							$code = 6;
							break;
					}

					if(!$res){
						$codes = [
							'chars' => 0,
							'value' => 1,
							'rules' => 2
						];
						$code = '9'. $codes[$this->type] . $code;
						$this->temp[$k][] = [$code, $item[2]];
						$temp[$k] = false;
					}
				}else{
					msg::set('boa.error.900', $this->field .' > '. $this->type .' > '. $vv);
				}
			}
			
			$result = $result || $temp[$k];
		}
		
		if(!$result){
			$join_and = boa::lang('boa.system.join_and');
			$join_or = boa::lang('boa.system.join_or');
			$toperr = [];

			foreach($this->temp as $k => $v){
				$suberr = [];
				foreach($v as $kk => $vv){
					$suberr[] = boa::lang('boa.error.'. $vv[0], $vv[1]);
				}
				$toperr[] = implode($join_and, $suberr);
			}
			$error = implode($join_or, $toperr);
			
			msg::set('boa.error.930', $this->label, $error);
		}
	}

	private function cls_checker(){
		if(!$this->checker){
			$this->checker = $this->load('checker');
		}
		return $this->checker;
	}

	private function cls_filter(){
		if(!$this->filter){
			$this->filter = $this->load('filter');
		}
		return $this->filter;
	}

	private function load($key){
		$mod = boa::env('mod');
		$file = BS_MOD ."$mod/validater/$key.php";
		if(file_exists($file)){
			$name = "\\mod\\$mod\\validater\\$key";
		}else{
			$name = "\\boa\\validater\\$key";
		}
		return new $name();
	}

	private function get_lng($key){
		$res = preg_match('/^[\w]+(\.[\w]+){2,}$/', $key);
		if($res){
			return boa::lang($key);
		}else{
			return $key;
		}
	}
}
?>