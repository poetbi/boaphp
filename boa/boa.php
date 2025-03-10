<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.boa.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

defined('BS_ROOT') or exit('BS_ROOT');
defined('BS_WWW') or exit('BS_WWW');
class boa{
	private static $env = [[
		'lng'  => 'zh-cn',
		'mod'  => 'home',
		'con'  => 'index',
		'act'  => 'index',
		'var' => [
			'page' => 1
		]
	]];
	private static $info = [];
	private static $obj = [];
	private static $mod = [];
	private static $con = [];
	private static $lang = [];
	private static $save;

	public static function init(){
		self::$info['call'] = 0;
		self::$info['time_start'] = microtime(true);
		self::$info['mem_start'] = memory_get_usage();
		set_error_handler(['\\boa\\boa', 'error']);
		set_exception_handler(['\\boa\\boa', 'exception']);
		spl_autoload_register(['\\boa\\boa', 'load']);
		register_shutdown_function(['\\boa\\boa', 'finish']);

		ob_start();
		self::conf();
		self::head();
		if(!file_exists(BS_MOD)) self::installer()->initlize();
		self::event()->trigger('init');
		ob_clean();
	}

	public static function start($env = []){
		if($env){
			foreach($env as $k => $v){
				self::$env[0][$k] = $v;
			}
		}
		try{
			self::init();
			self::type();
			if(!$env) self::route();
			self::call();
		}catch(\Throwable $e){
			msg::setEx($e);
		}catch(\Exception $e){
			msg::setEx($e);
		}
	}

	public static function call($key = null, $var = []){
		$env = [];
		if($key){
			$arr = explode('.', "..$key");
			$max = count($arr);
			$env = [
				'mod'  => $arr[$max - 3],
				'con'  => $arr[$max - 2],
				'act'  => $arr[$max - 1]
			];
			if(!$env['con']) $env['con'] = self::env('con');
			if(!$env['mod']) $env['mod'] = self::env('mod');
			if($var) $env['var'] = $var;
		}
		if($env) array_unshift(self::$env, $env);
		self::$info['call']++;
		self::mod(self::env('mod'));
		$act = self::env('act');
		$con = self::con();
		$res = $con->$act();
		self::event()->trigger('action');
		if($env) array_shift(self::$env);
		return $res;
	}

	public static function info($k = null, $v = null){
		switch(true){
			case $k === null && $v === null:
				return self::$info;
				break;

			case $v === null:
				return self::$info[$k];
				break;

			default:
				self::$info[$k] = $v;
		}
	}

	public static function env($k = null, $v = null){
		$env = current(self::$env);
		switch(true){
			case $k === null && $v === null:
				return $env;
				break;

			case $v === null:
				$arr = explode('.', $k);
				foreach($arr as $key){
					$env = $env[$key];
				}
				return $env;
				break;

			default:
				$arr = explode('.', $k);
				if(count($arr) > 1){
					self::$env[0][$arr[0]][$arr[1]] = $v;
				}else{
					self::$env[0][$k] = $v;
				}
		}
	}

	public static function route(){
		$router = self::router();
		self::$env[0] = array_merge(self::$env[0], $router->env());
	}

	public static function in_env(){
		return self::$env[1];
	}

	public static function lang(){
		$args = func_get_args();
		$key = array_shift($args);
		$lng = self::env('lng');
		$arr = explode('.', $key);

		$k = "{$arr[0]}.{$arr[1]}";
		if(!array_key_exists($k, self::$lang)){
			self::$lang[$k] = self::cache()->xget('language', [
				'mod' => $arr[0], 
				'file' => $arr[1], 
				'lng' => $lng
			]);
		}
		$lang = self::$lang[$k];

		for($i = 2; $i < count($arr); $i++){
			if(!array_key_exists($arr[$i], $lang)){
				return strtoupper($key);
			}else{
				$lang = $lang[$arr[$i]];
			}
		}

		foreach($args as $k => $v){
			$v = strip_tags($v, '<a><i>');
			$lang = preg_replace("/%$k/", $v, $lang);
		}
		$lang = preg_replace('/%\d/', '', $lang);

		return $lang;
	}

	public static function model($key){
		list($mod, $cls) = self::split($key);
		$cls = "\\mod\\$mod\\model\\$cls";
		return new $cls();
	}

	public static function const($key){
		$mod = self::env('mod');
		if(defined("\\$mod\\$key")){
			return constant("\\$mod\\$key");
		}else if(defined($key)){
			return constant($key);
		}
	}
	
	public static function getkey($val, $type = 0, $class = '', $prefix = ''){
		if($type == 1){
			$cls = new \ReflectionClass($class);
			$arr = $cls->getConstants();
		}else{
			if($class){
				$arr = get_defined_constants(true);
				$arr = $arr[$class];
			}else{
				$arr = get_defined_constants();
			}
		}
		foreach($arr as $k => $v){
			if($v === $val && strpos($k, $prefix) !== false){
				return $k;
			}
		}
	}

	public static function db($new = []){
		return self::__callStatic('database', $new);
	}

	public static function conf(){
		$config = BS_WWW .'cfg/config.php';
		if(file_exists($config)){
			$arr = include($config);
			foreach($arr as $k => $v){
				$k = strtoupper($k);
				define($k, $v);
			}
		}

		if(defined('LANGUAGE')) self::env('lng', strtolower(LANGUAGE));
		
		if(defined('DEBUG') && DEBUG){
			ini_set('display_errors', 'On');
		}else{
			error_reporting(0);
		}

		if(!defined('BS_BOA')) define('BS_BOA', BS_ROOT .'boa/');
		if(!defined('BS_MOD')) define('BS_MOD', BS_ROOT .'mod/');
		if(!defined('BS_VAR')){
			$www = rtrim(BS_WWW, '/');
			$www = substr(strrchr($www, '/'), 1);
			define('BS_VAR', BS_ROOT ."var/$www/");
		}
		if(!defined('WWW')){
			$root = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
			$www = preg_replace('/^'. preg_quote($root, '/') .'/', '', BS_WWW);
			define('WWW', $www);
		}
		if(!defined('WWW_RES')) define('WWW_RES', WWW .'res/');
		if(!defined('WWW_FILE')) define('WWW_FILE', WWW .'file/');
		ini_set('date.timezone', self::lang('boa.locale.timezone'));
	}

	public static function mod($mod){
		if(!in_array($mod, self::$mod)){
			self::$mod[] = $mod;
			$file = BS_MOD . "$mod/config.php";
			if(file_exists($file)){
				$arr = include($file);
				foreach($arr as $k => $v){
					$k = strtoupper($k);
					define("$mod\\$k", $v);
				}
			}
		}
		self::event()->trigger('module');
	}

	public static function con($key = null){
		if($key){
			list($mod, $con) = self::split($key);
		}else{
			$mod = self::env('mod');
			$con = self::env('con');
		}
		$key = "$mod.$con";
		if(!array_key_exists($key, self::$con)){
			$_file = $file = BS_MOD ."$mod/controller/$con.php";
			$_cls = $cls = "\\mod\\$mod\\controller\\$con";
			if(!file_exists($file)){
				$file = BS_MOD ."$mod/controller/_empty.php";
				$cls = "\\mod\\$mod\\controller\\_empty";
			}

			if(file_exists($file)){
				require_once($file);
				if(class_exists($cls, false)){
					self::$con[$key] = new $cls();
				}else{
					msg::set('boa.error.3', $_cls);
				}
			}else{
				if(defined('DEBUG') && DEBUG){
					msg::set('boa.error.2', $_file);
				}else{
					self::view()->lost();
				}
			}
		}
		self::event()->trigger('controller');
		return self::$con[$key];
	}

	public static function head(){
		header('Content-type: text/html; charset='. CHARSET);
		header('X-Powered-By: boaPHP (http://boasoft.top)');
		
		if(array_key_exists('HTTP_ORIGIN', $_SERVER) && defined('CORS')){
			header('Access-Control-Allow-Origin: '. CORS['origin']);
			header('Access-Control-Allow-Credentials: true');
			if(CORS['headers']){
				header('Access-Control-Allow-Headers: '. CORS['headers']);
			}
			if(CORS['methods']){
				header('Access-Control-Allow-Methods: '. CORS['methods']);
			}
		}
	}

	public static function lib($key, $args = null){
		list($mod, $cls) = self::split($key);
		$file = BS_MOD ."$mod/library/$cls.php";
		if(file_exists($file)){
			$cls = "\\mod\\$mod\\library\\$cls";
			return new $cls($args);
		}else{
			msg::set('boa.error.2', $file);
		}
	}

	public static function load($cls){
		$cls = str_replace('\\', '/', $cls);
		$file = BS_ROOT . "$cls.php";
		if(file_exists($file)){
			require_once($file);
		}else{
			msg::set('boa.error.2', $file);
		}
	}

	public static function error($no, $str, $file, $line){
		msg::setE($no, $str, $file, $line);
	}

	public static function exception($e){
		msg::setEx($e);
	}

	public static function save($path, $force = false){
		if($force) $path = chr(8) . $path;
		self::$save = $path;
	}

	public static function finish(){
		$path = self::$save;
		if($path){
			$force = substr($path, 0, 1) == chr(8) ? true : false;
			if($force){
				$path = substr($path, 1);
			}
			if(!file_exists($path) || $force){
				self::file()->write($path, ob_get_contents());
			}
		}
		self::$info['time_end'] = microtime(true);
		self::$info['mem_end'] = memory_get_usage();
		self::log()->save();
	}

	public static function debug($v, $k = '-'){
		if(!is_scalar($v)){
			if(is_resource($v)){
				$v = serialize($v);
			}else{
				$v = json_encode($v, JSON_UNESCAPED_UNICODE);
			}
		}
		$time = date(boa::lang('boa.locale.longtime'));
		$str = "[$time] $k : $v\r\n\r\n";
		file_put_contents(BS_VAR .'debug.txt', $str, FILE_APPEND);
	}

	public static function __callStatic($name, $cfg = []){
		if($cfg) $cfg = current($cfg);
		$const = strtoupper($name);
		$key = $name . self::arr2key($cfg);
		if(!array_key_exists($key, self::$obj)){
			$cfg = self::merge(self::const($const), $cfg);
			$name = '\\boa\\'. $name;
			self::$obj[$key] = new $name($cfg);
		}
		return self::$obj[$key];
	}

	public static function type(){
		$name = defined('MSG_TYPE_VAR') ? MSG_TYPE_VAR : '_msg';
		if(array_key_exists($name, $_REQUEST)){
			$type = $_REQUEST[$name];
			if($type){
				msg::set_type($type);
			}else{
				if(defined('MSG_TYPE')){
					msg::set_type(MSG_TYPE);
				}
			}
		}
	}
	
	private static function split($key){
		if(strpos($key, '.') === false){
			$key = self::env('mod') .'.'. $key;
		}
		$arr = explode('.', $key, 2);
		return $arr;
	}

	private static function merge($cfg, $new = []){
		if(!$cfg) $cfg = [];
		if(is_array($new)){ // array
			return array_merge($cfg, $new);
		}else if($new !== null){ // string
			return $new;
		}else{
			return $cfg;
		}
	}

	private static function arr2key($arr = []){
		if($arr){
			if(is_array($arr)){
				ksort($arr);
			}
			$str = json_encode($arr);
			$key = crc32($str);
		}else{
			$key = '';
		}
		return $key;
	}
}
?>