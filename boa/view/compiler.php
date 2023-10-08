<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.view.compiler.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\view;

use boa\boa;
use boa\msg;

class compiler{
	private $html;
	private $tags = [];
	private $tag;
	private $tree = [], $temp = [], $res = 1;
	private $_s = '\{';
	private $_e = '\s*\}';
	private $cfg = [
		'static' => true
	];
	
	public function __construct(){
		$this->tag = '/'. chr(8) .'[a-f0-9]{16}'. chr(8) .'/';
		$cfg = unserialize(boa::constant('COMPILER'));
		if(is_array($cfg)){
			$this->cfg = array_merge($this->cfg, $cfg);
		}
	}

	public function file($file, $cfile){		
		$this->html = file_get_contents($file);
		$this->tags();
		$this->compile();
		boa::file()->write($cfile, $this->html);
	}

	public function str($str){
		$this->html = $str;
		$this->tags();
		$this->compile();
		return $this->html;
	}

	private function compile(){
		$this->prepare();
		$this->reverse();
		$this->obverse();
		$this->header();
	}

	private function header(){
		if(strpos($this->html, '$_SESSION') !== false){
			if(strpos($this->html, 'boa::session') === false){
				$session = 'boa::session();';
			}
		}
		$this->html = '<?php use \\boa\\boa;'. $session .'$_ENV=boa::env(); ?>'. $this->html;
	}

	private function prepare(){
		$re = '/'. $this->_s .'inc\s+([^\{\}]+?)'. $this->_e .'/';
		do{
			$depth++;
			$num = preg_match_all($re, $this->html, $arr);
			if($num){
				foreach($arr[1] as $k => $v){
					$sub = explode('.', '..'. $v);
					$max = count($sub) - 1;
					$inc = $sub[$max];
					$con = $sub[$max - 1] ? $sub[$max - 1] : boa::env('con');
					$mod = $sub[$max - 2] ? $sub[$max - 2] : boa::env('mod');

					$str = '';
					$file = BS_WWW ."tpl/$mod/$con/$inc.html";
					if(file_exists($file)){
						$str = file_get_contents($file);
					}else{
						$file = BS_MOD ."$mod/view/$con/$inc.html";
						if(file_exists($file)){
							$str = file_get_contents($file);
						}
					}

					if($str){
						$this->html = str_replace($arr[0][$k], $str, $this->html);
					}else{
						msg::set('boa.error.2', BS_WWW ."tpl/$mod/$con/$inc.html");
					}
				}
			}
		}while($num && $depth < 10);
	}

	private function reverse(){
		do{
			$arr = [];
			foreach($this->tags as $i => $tag){
				$num = preg_match_all($tag[0], $this->html, $a);
				if($num){
					foreach($a[0] as $s){
						$x = chr(8) . substr(md5($s . ++$j), 8, 16) . chr(8);
						$this->temp[$x] = [
							'str' => $s,
							'tag' => $i,
							'res' => 1
						];
						$arr[] = $x;
					}
				}
			}
			if($arr){
				foreach($arr as $x){
					$this->html = preg_replace('/'. preg_quote($this->temp[$x]['str'], '/') .'/', $x, $this->html, 1);
				}
				$this->tree[] = $arr;
			}
		}while($arr);
	}

	private function obverse(){
		$m = count($this->tree) - 1;
		for($i = $m; $i >= 0; $i--){
			foreach($this->tree[$i] as $k){
				$b = $this->temp[$k];
				$r = $this->tags[$b['tag']];
				
				if($i >= 1){
					$num = preg_match_all($this->tag, $b['str'], $c);
					if($num){
						foreach($c[0] as $x){
							$this->temp[$x]['res'] = 2;
							$this->temp[$x]['top'] = $b['tag'];
						}
					}
				}

				if(is_array($r[2])){
					$this->res = $b['res'];
					$e = preg_replace_callback($r[0], $r[2], $b['str']);
				}else{
					$e = preg_replace($r[0], $r[$b['res']], $b['str']);
				}
				$this->html = str_replace($k, $e, $this->html);
			}
		}
	}

	private function cb_var($m){
		$str = '$'. $this->arr_format($m[1]);
		if($this->res == 1){
			$str = "<?php echo $str; ?>";
		}
		return $str;
	}

	private function cb_con($m){
		$mod = boa::env('mod');
		if(defined($mod .'\\'. $m[1])){
			$str = "\\$mod\\". $m[1];
		}else if(defined($m[1])){
			$str = $m[1];
		}else{
			return '{'. $m[1] .'}';
		}
		if($this->res == 1){
			$str = "<?php echo $str; ?>";
		}
		return $str;
	}

	private function cb_lang($m){
		$arr = preg_split('/\s+/', $m[1]);
		if($this->cfg['static'] && !preg_match($this->tag, $m[1])){
			$str = call_user_func_array(['\\boa\\boa', 'lang'], $arr);
			if($this->res != 1){
				$str = "'". str_replace("'", "\'", $str) ."'";
			}
		}else{
			$max = count($arr);
			$str = "boa::lang('". $arr[0] ."'";
			for($i = 1; $i < $max; $i++){
				$str .= ','. $this->var_format($arr[$i]);
			}
			$str .= ")";
			if($this->res == 1){
				$str = "<?php echo $str; ?>";
			}
		}
		return $str;
	}
	
	private function cb_if($m){
		$str = $this->cb_sub_if($m[1]);
		$str = "if($str){";
		if($this->res == 1){
			$str = "<?php $str ?>";
		}
		return $str;
	}
	
	private function cb_elseif($m){
		$str = $this->cb_sub_if($m[1]);
		$str = "}else if($str){";
		if($this->res == 1){
			$str = "<?php $str ?>";
		}
		return $str;
	}
	
	private function cb_sub_if($str){
		//===|!==|==|!=|<=|>=|<|>|eq|neq|lt|gt|le|ge
		$str = preg_replace(['/ and /i', '/ or /i'], [' && ', ' || '], $str);
		$re = [
			'/\s+eq\s+/',
			'/\s+neq\s+/',
			'/\s+lt\s+/',
			'/\s+gt\s+/',
			'/\s+le\s+/',
			'/\s+ge\s+/'
		];
		$rp = ['==', '!=', '<', '>', '<=', '>='];
		$str = preg_replace($re, $rp, $str);
		return $str;
	}

	private function cb_list($m){
		$arr = preg_split('/\s+/', $m[1]);
		$a = $this->arr_format($arr[0]);
		switch(count($arr)){
			case 3 : 
				$b = $arr[1] .'=>';
				$c = $arr[2];
				break;

			case 2 :
				$b = '';
				$c = $arr[1];
				break;

			default :
				$b = '';
				$c = '$_';
		}
		$str = "foreach($a as $b$c){";
		if($this->res == 1){
			$str = "<?php $str ?>";
		}
		return $str;
	}

	private function cb_fun($m){
		if(!function_exists($m[1])){
			return '{'. $m[1] . $m[2] .'}';
		}

		$fun = $m[1];
		$arg = $m[2];
		$args = [];
		$temp = $this->arg_str($arg);

		$arr = preg_split('/\s+/', trim($arg));
		foreach($arr as $v){
			if(array_key_exists($v, $temp)){
				$args[] = $temp[$v];
			}else{
				$args[] = $this->var_format($v);
			}
		}
		$str = "$fun(". implode(', ', $args) .")";
		if($this->res == 1){
			$str = "<?php echo $str ?>";
		}
		return $str;
	}

	private function cb_boa($m){
		$key = $m[1];
		$arg = $m[2];
		$temp = $this->arg_str($arg);
		$args = '';

		$arr = preg_split('/\s+/', $arg);
		list($mod, $model, $method) = explode('.', $arr[0], 3);

		$max = count($arr);
		for($i = 1; $i < $max; $i++){
			$v = $arr[$i];
			if(array_key_exists($v, $temp)){
				$v = $temp[$v];
			}else{
				$v = $this->var_format($v);
			}
			$args .= ", $v";
		}
		$args = ltrim($args, ', ');

		$str = "<?php \$$key = boa::model('$mod.$model')->$method($args); ?>";
		return $str;
	}

	private function arg_str(&$arg){
		$temp = [];
		preg_match_all('/(?<=^|\s)[\'"].+?[\'"](?=\s|$)/', $arg, $arr);
		foreach($arr[0] as $k => $v){
			$k = chr(8) .$k. chr(8);
			$temp[$k] = $v;
			$arg = str_replace($v, $k, $arg);
		}
		return $temp;
	}

	private function var_format($v){
		$v = str_replace('\\s', ' ', $v);
		if(!$v){
			return '';
		}
		
		$is_arr = preg_match('/^(array\(.+?\)|\[.+?\])$/i', $v);
		if($is_arr){
			return $v;
		}
		
		$is_num = preg_match('/^([\-]?\d+[\.\d]*|true|false)$/i', $v);
		if($is_num){
			return $v;
		}
		
		$is_var = preg_match($this->tag, $v);
		if($is_var){
			return $v;
		}

		$v = trim($v, '"\' ');
		$v = str_replace("'", "\'", $v);
		return "'$v'";
	}

	private function arr_format($v){
		$arr = explode('.', $v);
		$v = $arr[0];
		$max = count($arr);
		for($i = 1; $i < $max; $i++){
			$key = $arr[$i];
			$s = preg_match('/^([\-]?\d+)$/', $key) ? '': "'";
			$v .= "[$s". $key ."$s]";
		}
		return $v;
	}

	private function tags(){
		$exclude = 'if|else|list';
		$arr['CON']  = ['/'. $this->_s .'([A-Z_0-9]+?)'. $this->_e .'/', '', [$this, 'cb_con']];
		$arr['VAR']  = ['/'. $this->_s .'\$([\w\.\+\-]+?)'. $this->_e .'/', '', [$this, 'cb_var']];
		$arr['LANG'] = ['/'. $this->_s .'@\s*([^\{\}]+?)'. $this->_e .'/', '', [$this, 'cb_lang']];
		$arr['IF']   = ['/'. $this->_s .'if\s+([^\{\}]+?)'. $this->_e .'/', '', [$this, 'cb_if']];
		$arr['ELIF'] = ['/'. $this->_s .'else\s*if\s+([^\{\}]+?)'. $this->_e .'/', '', [$this, 'cb_elseif']];
		$arr['ELSE'] = ['/'. $this->_s .'else'. $this->_e .'/', '<?php }else{ ?>', '}else{'];
		$arr['-IF']  = ['/'. $this->_s .'\/if'. $this->_e .'/', '<?php } ?>', '}'];
		$arr['LIST'] = ['/'. $this->_s .'list\s+([^\{\}]+?)'. $this->_e .'/', '', [$this, 'cb_list']];
		$arr['-LIST']= ['/'. $this->_s .'\/list'. $this->_e .'/', '<?php } ?>', '}'];
		$arr['BOA']  = ['/'. $this->_s .'\$(\w+)\s+([^\{\}]+?)'. $this->_e .'/', '', [$this, 'cb_boa']];
		$arr['FUN']  = ['/'. $this->_s .'((?!'. $exclude .')\w+(?!\s*:))(\s+[^\{\}]+?)?'. $this->_e .'/', '', [$this, 'cb_fun']]; //excludes template tags and javascript object
		$this->tags = $arr;
	}
}
?>
