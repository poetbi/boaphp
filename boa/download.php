<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.download.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class download extends base{
	protected $cfg = [
		'header' => [],
		'expire' => 0,
		'size' => 2, //MB, 0=unlimited
		'exts' => 'jpg,png,gif',
		'path' => BS_WWW .'file/',
		'name' => null,
		'ext' => null,
		'default' => 'Y/m/d/Hisv',
		'break' => false,
		'auto' => false
	];
	private $files = [];
	private $i = 0;
	private $obj;

	public function __construct($cfg = []){
		parent::__construct($cfg);

		$arr = [];
		if($this->cfg['expire'] > 0){
			$arr['execute'] = $this->cfg['expire'];
		}
		if($this->cfg['header']){
			$arr['header'] = $this->cfg['header'];
		}
		$this->obj = boa::http($arr);
	}

	public function get_file($i = 0){
		return $this->files[$i];
	}

	public function get_files(){
		return $this->files;
	}

	public function one($file, $name = null, $ext = null){
		if($name) $this->cfg('name', $name);
		if($ext) $this->cfg('ext', $ext);
		$this->files = [];
		$this->i = 0;

		$res = $this->download($file);
		return $res;
	}

	public function more($files, $name = [], $ext = null){
		if($name) $this->cfg('name', $name);
		if($ext) $this->cfg('ext', $ext);
		$this->files = [];

		$res = true;
		foreach($files as $k => $v){
			$this->i = $k;
			$res = $this->download($v) && $res;
			if(!$res && $this->cfg['break']) return false;
		}
		return $res;
	}
	
	private function download($file){
		$this->files[$this->i]['name'] = $file;

		$ext = strtolower(substr(strrchr($file, '.'), 1));
		$exts = str_replace(' ', '', strtolower($this->cfg['exts']));
		if(!preg_match("/(^|,)$ext(,|$)/", $exts)){
			$this->files[$this->i]['error'] = boa::lang('boa.error.123', $ext);
			return false;
		}

		$header = $this->obj->head($file);
		$size = $header['Content-Length'];
		if($this->cfg['size'] > 0 && $size > $this->cfg['size'] * 1048576){
			$this->files[$this->i]['error'] = boa::lang('boa.error.122', $this->cfg['size']);
			return false;
		}

		$path = $this->path($ext);
		$this->obj->get($file);
		if($this->obj->get_status() == 200){
			$body = $this->obj->get_body();
			if($this->cfg['auto']){
				$im = boa::image();
				$im->open($body, 1);
				$res = $im->save($path);
				$im->clear();
			}else{
				$res = file_put_contents($path, $body);
			}
			if($res){
				$this->files[$this->i]['type'] = $header['Content-Type'];
				$this->files[$this->i]['size'] = $size;
				$this->files[$this->i]['file'] = $path;
				return true;
			}else{
				$this->files[$this->i]['error'] = boa::lang('boa.error.121', $file);
				return false;
			}
		}else{
			$this->files[$this->i]['error'] = $this->obj->get_error();
			return false;
		}
	}

	private function path($ext){
		if(is_array($this->cfg['name'])){
			$name = $this->cfg['name'][$this->i];
		}else{
			$name = $this->cfg['name'];
		}
		if($this->cfg['ext']){
			if(is_array($this->cfg['ext'])){
				$ext = $this->cfg['ext'][$this->i];
			}else{
				$ext = $this->cfg['ext'];
			}
		}

		if(!$name){
			$date = new \DateTimeImmutable();
			$name = $date->format($this->cfg['default']);
		}else{
			$reg = preg_quote($this->cfg['path'], '/');
			$name = preg_replace("/^$reg/", '', $name);
			$name = str_replace('../', '', $name);
		}

		$path = $this->cfg['path'] ."$name.$ext";
		$dir = dirname($path);
		if($dir && !file_exists($dir)) mkdir($dir, 0777, true);
		return $path;
	}
}
?>