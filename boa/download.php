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
		'name' => null
	];
	private $files = [];
	private $i = 0;
	private $obj;

	public function __construct($cfg = []){
		parent::__construct($cfg);

        $this->format_exts();

		$arr = [];
		if($this->cfg['expire'] > 0){
			$arr['execute'] = $this->cfg['expire'];
		}
		if($this->cfg['header']){
			$arr['header'] = $this->cfg['header'];
		}
		$this->obj = boa::http($arr);
	}

	public function cfg($k = null, $v = null){
		if($k == 'exts' && $v !== null){
			$this->format_exts();
		}
		return parent::cfg($k, $v);
	}

	public function get_file($i = 0){
		return $this->files[$i];
	}

	public function get_files(){
		return $this->files;
	}

	public function one($file, $save = ''){
		if($save){
			$this->cfg('name', $save);
		}
		$this->files = [];
		
		$res = $this->download($file);
		return $res;
	}

	public function more($files, $save = []){
		if($save){
			$this->cfg('name', $save);
		}
		$this->files = [];

		$res = true;
		foreach($files as $k => $v){
			$this->i = $k;
			$res = $this->download($v) && $res;
		}
		return $res;
	}
	
	private function download($file){
		$this->files[$this->i]['name'] = $file;

		$ext = strtolower(substr(strrchr($file, '.'), 1));
		if(!preg_match("/(^|,)$ext(,|$)/", $this->cfg['exts'])){
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
			$res = boa::file()->write($path, $body);
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

		if(!$name){
			$micro = substr(strrchr(microtime(true), '.'), 1);
			$name = date('Y/m/d/His', time()) ."$micro.$ext";
		}else{
			$reg = preg_quote($this->cfg['path'], '/');
			$name = preg_replace("/^$reg/", '', $name);
			$name = ltrim($name, ' /');
		}

		$path = $this->cfg['path'] . $name;
		return $path;
	}

	private function format_exts(){
		$this->cfg['exts'] = str_replace(' ', '', strtolower($this->cfg['exts']));
	}
}
?>