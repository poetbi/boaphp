<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.upload.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class upload extends base{
	protected $cfg = [
		'strict' => false,
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

	public function __construct($cfg = []){
		parent::__construct($cfg);
	}

	public function get_file($i = 0){
		return $this->files[$i];
	}

	public function get_files(){
		return $this->files;
	}

	public function base64($field, $name = null, $ext = null){
		if($name) $this->cfg('name', $name);
		if($ext) $this->cfg('ext', $ext);
		$this->files = [];
		$this->i = 0;

		$file = $_POST[$field];
		if($file){
			$mime = str_replace('data:', '', strstr($file, ';base64,', true));
			$this->files[0]['type'] = $mime;

			$body = base64_decode(substr(strstr($file, ';base64,'), 8));
			$size = strlen($body);
			$this->files[0]['size'] = $size;
			
			if($size < 1){
				$this->files[0]['error'] = boa::lang('boa.error.171');
			}

			if($this->cfg['size'] > 0 && $size > $this->cfg['size'] * 1048576){
				$this->files[0]['error'] = boa::lang('boa.error.172', $this->cfg['size']);
				return false;
			}

			$ext = $this->cfg['ext'] ? $this->cfg['ext'] : $this->check_mime($mime);
			if(!$ext){
				$this->files[0]['error'] = boa::lang('boa.error.173', $mime);
				return false;
			}

			$path = $this->path($ext);
			$res = boa::file()->write($path, $body);
			if($res){
				$this->files[0]['file'] = $path;
				return true;
			}else{
				$this->files[0]['error'] = boa::lang('boa.error.170', $field);
				return false;
			}
		}
		return false;
	}

	public function one($field, $name = null, $ext = null){
		if($name) $this->cfg('name', $name);
		if($ext) $this->cfg('ext', $ext);
		$this->files = [];
		$this->i = 0;

		$file = $_FILES[$field];
		$this->files[0] = $file;
		$res = $this->upload($file);
		return $res;
	}

	public function more($field, $name = [], $ext = null){
		if($name) $this->cfg('name', $name);
		if($ext) $this->cfg('ext', $ext);
		$this->files = [];

		$res = true;
		$files = $_FILES[$field];
		foreach($files['tmp_name'] as $k => $v){
			$file = [
				'tmp_name' => $v,
				'name' => $files['name'][$k],
				'type' => $files['type'][$k],
				'size' => $files['size'][$k],
				'error' => $files['error'][$k]
			];
			$this->i = $k;
			$this->files[$this->i] = $file;
			$res = $this->upload($file) && $res;
			if(!$res && $this->cfg['break']) return false;
		}
		return $res;
	}

	private function upload($file){
		unset($this->files[$this->i]['full_path']);
		if($file['error'] == UPLOAD_ERR_OK){
			unset($this->files[$this->i]['error']);
		}else{
			switch($file['error']){
				case UPLOAD_ERR_INI_SIZE :
					$errno = 174;
					break;

				case UPLOAD_ERR_FORM_SIZE :
					$errno = 175;
					break;

				case UPLOAD_ERR_PARTIAL :
					$errno = 176;
					break;

				case UPLOAD_ERR_NO_FILE :
					$errno = 177;
					break;

				case UPLOAD_ERR_NO_TMP_DIR :
					$errno = 178;
					break;

				case UPLOAD_ERR_CANT_WRITE :
					$errno = 179;
					break;
					
				case UPLOAD_ERR_EXTENSION :
					$errno = 180;
					break;
			}
			if($errno) $this->files[$this->i]['error'] = boa::lang("boa.error.$errno", $file['name']);
			return false;
		}

		if(!is_uploaded_file($file['tmp_name'])){
			$this->files[$this->i]['error'] = boa::lang('boa.error.171', $file['name']);
			return false;
		}

		if($this->cfg['size'] > 0 && $file['size'] > $this->cfg['size'] * 1048576){
			$this->files[$this->i]['error'] = boa::lang('boa.error.172', $this->cfg['size']);
			return false;
		}

		$ext = strtolower(substr(strrchr($file['name'], '.'), 1));
		$exts = str_replace(' ', '', strtolower($this->cfg['exts']));
		if(!preg_match("/(^|,)$ext(,|$)/", $exts)){
			$this->files[$this->i]['error'] = boa::lang('boa.error.173', $ext);
			return false;
		}else if($this->cfg['strict'] && $file['type']){
			$mime = util::mimetype($ext);
			if($mime != $file['type']){
				$this->files[$this->i]['error'] = boa::lang('boa.error.173', $file['type']);
				return false;
			}
		}

		$path = $this->path($ext);
		if($this->cfg['auto']){
			$im = boa::image();
			$im->open($file['tmp_name']);
			$res = $im->save($path);
			$im->clear();
		}else{
			$res = move_uploaded_file($file['tmp_name'], $path);
		}
		if($res){
			$this->files[$this->i]['file'] = $path;
			return true;
		}else{
			$this->files[$this->i]['error'] = boa::lang('boa.error.170', $file['name']);
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

	private function check_mime($mime){
		$exts = str_replace(' ', '', strtolower($this->cfg['exts']));
		$exts = explode(',', $exts);
		foreach($exts as $ext){
			$type = util::mimetype($ext);
			if($mime == $type) return $ext;
		}
		return false;
	}
}
?>