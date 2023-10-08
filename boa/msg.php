<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.msg.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class msg{
	private static $auto = 1;
	private static $type = 'msg'; //xml, json, str, msg, cli, jsonp
	private static $display = 'php_exception,php_error'; //php_exception,php_warning,php_error,php_deprecated,php_strict
	private static $data = [];
	private static $msg = [];

	public static function set_data($data = []){
		self::$data = $data;
	}

	public static function set_type($type){
		self::$type = $type;
	}
	
	public static function begin(){
		self::$auto = 0;
	}

	public static function commit(){
		self::$auto = 1;
		self::out();
	}

	public static function setE($no, $str, $file, $line){
		switch($no){
			case E_USER_NOTICE:
			case E_NOTICE:
				$type = 'php_notice';
			break;

			case E_USER_WARNING:
			case E_WARNING:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
				$type = 'php_warning';
			break;

			case E_USER_ERROR:
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_RECOVERABLE_ERROR:
			case E_PARSE:
				$type = 'php_error';
			break;

			case E_USER_DEPRECATED:
			case E_DEPRECATED:
				$type = 'php_deprecated';
			break;

			case E_STRICT:
				$type = 'php_strict';
			break;
		}

		$notice = defined('MSG_LOG_NOTICE') ? MSG_LOG_NOTICE : false;
		if($type && ($notice || $type != 'php_notice')){
			$arr['type'] = $type;
			$arr['key'] = '-'. $no;
			$arr['msg'] = self::filter_path($str, false);
			
			$log = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			array_shift($log);
			$arr['log'] = self::handle_log($log);

			boa::log()->set($type, '['. $arr['key'] .']'. current($arr['log']) .' '. strip_tags($arr['msg']));

			if(self::is_display($type)){
				self::$msg[] = $arr;
				self::out('error');
			}
		}
	}

	public static function setEx($e){
		if($e->getCode() == 0){
			$no = -9999;
		}else{
			$no = '-'. $e->getCode();
		}
		$arr['type'] = 'php_exception';
		$arr['key'] = $no;
		$arr['msg'] = self::filter_path($e->getMessage(), false);

		$log = $e->getTrace();
		array_unshift($log, [
			'file' => $e->getFile(), 
			'line' => $e->getLine()
		]);
		$arr['log'] = self::handle_log($log);

		boa::log()->set($arr['type'], '['. $arr['key'] .']'. current($arr['log']) .' '. strip_tags($arr['msg']));

		if(self::is_display('php_exception')){
			self::$msg[] = $arr;
			self::out('error');
		}
	}

	public static function set(){
		$args = func_get_args();
		$num = count($args);
		if($num < 1){
			return false;
		}else if($num == 1 && substr_count($args[0], '.') < 2){
			$arr = [
				'key' => '1',
				'type' => 'error',
				'msg' => $args[0]
			];
		}else{
			foreach($args as $i => $v){
				if($i > 0){
					$args[$i] = '<i>'. self::filter_path($v) .'</i>';
				}
			}
			$arr['msg'] = call_user_func_array(['\\boa\\boa', 'lang'], $args);

			$key = array_shift($args);
			$code = substr(strrchr($key, '.'), 1);
			$arr['key'] = intval($code);
			$type = explode('.', $key);
			$arr['type'] = $type[1];
		}

		if(defined('DEBUG') && DEBUG){
			$log = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			array_shift($log);
			$arr['log'] = self::handle_log($log);
		}else{
			$arr['log'] = [];
		}

		boa::log()->set($arr['type'], '['. $arr['key'] .']'. current($arr['log']) .' '. strip_tags($arr['msg']));
	
		self::$msg[] = $arr;
		self::out($arr['type']);
	}

	private static function out($type = null){
		if(!self::$auto){
			return false;
		}

		if(!self::$msg){
			return false;
		}

		$msg = self::$msg;
		self::$msg = [];

		$data = self::$data;
		self::$data = [];

		$view = boa::view();
		switch(self::$type){
			case 'msg':
				if(!$type){
					$v = current($msg);
					$type = $v['type'];
				}
				$view->msg($msg, $type, $data);
				break;

			case 'str':
				$class = defined('MSG_STR_CLASS') ? MSG_STR_CLASS : 'boa_msg';
				foreach($msg as $v){
					$log = self::format_log($v['log']);
					$str .= '<p class="'. $class .'">['. $v['key'] .']'. $v['msg'] . $log .'</p>';
				}
				$view->str($str);
				break;

			case 'json':
				$v = current($msg);
				$log = self::format_log($v['log']);
				$view->json($data, $v['key'], $v['msg'] . $log);
				break;

			case 'jsonp':
				$v = current($msg);
				$cb = defined('MSG_JSONP_CB') ? MSG_JSONP_CB : 'message';
				$log = self::format_log($v['log']);
				$view->jsonp($cb, $data, $v['key'], $v['msg'] . $log);
				break;

			case 'xml':
				$v = current($msg);
				$log = self::format_log($v['log']);
				$view->xml($data, $v['key'], $v['msg'] . $log);
				break;

			case 'cli':
				foreach($msg as $v){
					$log = self::format_log($v['log'], "\r\n\t");
					$str .= '*['. $v['key'] .']'. strip_tags($v['msg']) . $log ."\r\n";
				}
				$view->cli($str);
				break;
		}
	}

	private static function handle_log($log){
		foreach($log as $k => $v){
			$item = '';
			if(array_key_exists('file', $v)){
				$item .= self::filter_path($v['file']);
			}
			if(array_key_exists('line', $v)){
				$item .= '['. $v['line'] .'] : ';
			}
			if(array_key_exists('class', $v)){
				$item .= $v['class'] . $v['type'];
			}
			if(array_key_exists('function', $v)){
				$item .= $v['function'] .'()';
			}
			$log[$k] = $item;
		}
		return $log;
	}

	private static function filter_path($str, $start = true){
		$tag = $start ? '^' : '';
		$str = str_replace('\\', '/', $str);
		$str = preg_replace('/'. $tag . preg_quote(BS_ROOT, '/') .'/', '', $str);
		return $str;
	}

	private static function is_display($type){
		$types = defined('MSG_DISPLAY') ? MSG_DISPLAY : self::$display;
		$types = ','. str_replace(' ', '', $types) .',';
		if(strpos($types, $type) !== false){
			return true;
		}
	}

	private static function format_log($log, $br = '<br>'){
		if(is_array($log)){
			$str = $br .'@'. implode($br .'@', $log);
		}else{
			$str = $br .'@'. $log;
		}
		return $str;
	}
}
?>