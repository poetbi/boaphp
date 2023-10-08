<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.view.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class view{
	private $remain = ['__file', '__name', 'return'];
	private $var = [];
	private $cfg = [
		'xml_root' => 'boa'
	];

	public function __construct(){
		$cfg = unserialize(boa::constant('VIEW'));
		if(is_array($cfg)){
			$this->cfg = array_merge($this->cfg, $cfg);
		}
	}

	public function assign($k, $v){
		if(in_array($k, $this->remain)){
			msg::set('boa.error.8', $k);
		}else{
			$act = boa::env('act');
			$this->var[$act][$k] = $v;
		}
	}

	public function cli($str, $clean = true, $exit = true){
		if($clean){
			ob_clean();
		}

		fwrite(STDOUT, $str);

		if($exit){
			exit();
		}
	}

	public function str($str, $exit = true){
		echo $str;

		if($exit){
			exit();
		}
	}

	public function json($data = [], $code = 0, $msg = 'OK', $return = false){
		ob_clean();
		header('Content-type: application/json; charset='. CHARSET);

		$num = is_array($data) ? count($data) : -1;
		$arr = [
			'code' => $code,
			'msg' => $msg,
			'data' => $data,
			'num' => $num
		];

		$str = boa::json()->encode($arr, JSON_UNESCAPED_UNICODE);
		if($return){
			return $str;
		}else{
			echo $str;
			exit();
		}
	}

	public function page($page, $url = null, $number = 10, $first = true, $last = true, $prev = false, $next = false){
		$obj = new \boa\view\page();
		if($url){
			$obj->cfg('url', $url);
		}
		$str = $obj->get($page, $number, $first, $last, $prev, $next);
		return $str;
	}

	public function html($tpl = '', $return = false){
		if(!$tpl){
			$con = boa::env('con');
			$act = boa::env('act');
			$tpl = "$con/$act";
		}

		$__file = $this->cache_file($tpl);
		if($__file && file_exists($__file)){
			header('Content-type: text/html; charset='. CHARSET);

			$act = boa::env('act');
			if($this->var[$act]){
				extract($this->var[$act]);
			}

			msg::set_type('str');
			require($__file);
			if($return){
				return ob_get_contents();
			}
		}else{
			msg::set('boa.error.2', $__file);
		}
	}

	public function xml($data = [], $code = 0, $msg = 'OK', $return = false){
		ob_clean();
		header('Content-type: application/xml; charset='. CHARSET);

		$num = is_array($data) ? count($data) : -1;
		$root = $this->cfg['xml_root'];
		$arr = [
			$root => [
				'code' => $code,
				'msg' => $msg,
				'data' => $data,
				'num' => $num
			]
		];

		$str = boa::xml()->write($arr);
		if($return){
			return $str;
		}else{
			echo $str;
			exit();
		}
	}

	public function jsonp($callback, $data = [], $code = 0, $msg = 'OK', $return = false){
		ob_clean();
		header('Content-type: text/javascript; charset='. CHARSET);

		$num = is_array($data) ? count($data) : -1;
		$arr = [
			'code' => $code,
			'msg' => $msg,
			'data' => $data,
			'num' => $num
		];

		$str = boa::json()->encode($arr, JSON_UNESCAPED_UNICODE);
		$str = "$callback($str);";
		if($return){
			return $str;
		}else{
			echo $str;
			exit();
		}
	}

	public function jump($url, $sec = 0, $tip = null, $clean = true, $exit = true){
		if($clean) ob_clean();
		$this->assign('url', $url);
		$this->assign('sec', $sec);
		$this->assign('tip', $tip);
		$this->require_file('jump');
		if($exit) exit();
	}

	public function msg($msg, $type = 'error', $data = [], $clean = true, $exit = true){
		if($clean) ob_clean();
		$this->assign('msg', $msg);
		$this->assign('data', $data);
		$this->require_file($type);
		if($exit) exit();
	}

	public function lost($url = ''){
		ob_clean();
		if(!$url){
			$url = $_SERVER['REQUEST_URI'];
		}
		header("HTTP/1.1 404 Not Found");
		$this->assign('url', $url);
		$this->require_file('404');
		exit();
	}

	public function error(){
		ob_clean();
		header("HTTP/1.1 500 Internal Error");
		$this->require_file('500');
		exit();
	}

	public function file($file, $name = ''){
		ob_end_clean();
		if($file && file_exists($file)){
			if(!$name){
				$name = basename($file);
			}
			$name = rawurlencode($name);
			header('Pragma: public');
			header('Content-Transfer-Encoding: binary');
			header('Content-type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'. $name .'"');
			header('Content-length: '. filesize($file));
			readfile($file);
			exit();
		}else{
			msg::set('boa.error.2', $file);
		}
	}

	private function require_file($__name){
		header('Content-type: text/html; charset='. CHARSET);

		$act = boa::env('act');
		if($this->var[$act]){
			extract($this->var[$act]);
		}

		msg::set_type('str');
		$tpl = "msg/$__name";
		$__file = $this->cache_file($tpl, true);
		if($__file && file_exists($__file)){
			require($__file);
		}else{
			require(BS_BOA . "view/$tpl.php");
		}
	}

	private function cache_file($tpl, $silence = false){
		$mod = boa::env('mod');
		$file = BS_WWW ."tpl/$mod/$tpl.html";
		if(file_exists($file)){
			$mtime = filemtime($file);
		}else{
			$file = BS_MOD ."$mod/view/$tpl.html";
			if(file_exists($file)){
				$mtime = filemtime($file);
			}
		}

		if($mtime > 0){
			$__file = BS_VAR ."view/$mod/$tpl.php";
			if(
				(defined('DEBUG') && DEBUG)
				 || !file_exists($__file)
				 || $mtime > filemtime($__file)
			){
				$view = new \boa\view\compiler();
				$view->file($file, $__file);
			}
		}else{
			if(!$silence){
				msg::set('boa.error.2', BS_WWW ."tpl/$mod/$tpl.html");
			}
			$__file = null;
		}
		return $__file;
	}
}
?>