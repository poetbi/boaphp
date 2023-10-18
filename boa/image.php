<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.image.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class image{
	private $obj;

	public function __construct($cfg = []){
		if(!$cfg['driver']) $cfg['driver'] = 'gd';

		$driver = '\\boa\\image\\driver\\'. $cfg['driver'];
		$this->obj = new $driver($cfg);
	}
	
	public function cfg($k = null, $v = null){
		return $this->obj->cfg($k, $v);
	}
	
	public function open($img){
		$info = getimagesize($img);
		if(!in_array($info[2], [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG])){
			msg::set('boa.error.161', $img);
		}
		$this->obj->set_image($img, $info);
		return $this;
	}

	public function watermark($type = 0){
		if($type == 0) $type = $this->obj->cfg('wm_type');
		if($type > 0){
			$this->obj->watermark($type);
		}
		return $this;
	}

	public function thumbnail($width = 0, $height = 0){
		if($width == 0) $width = $this->obj->cfg('tb_width');
		if($height == 0) $height = $this->obj->cfg('tb_height');
		$this->scale($width, $height);
		return $this;
	}

	public function scale($width, $height = -1){
		$this->obj->scale($width, $height);
		return $this;
	}

	public function crop($width, $height, $x = 0, $y = 0){
		$this->obj->crop($width, $height, $x, $y);
		return $this;
	}

	public function rotate($angle, $bgcolor = '#FFFFFF'){
		$this->obj->rotate($angle, $bgcolor);
		return $this;
	}

	public function flatten($bgcolor = '#FFFFFF'){
		$this->obj->flatten($bgcolor);
		return $this;
	}

	public function flip($mode = 0){
		$this->obj->flip($mode);
		return $this;
	}

	public function text($text, $cfg = []){
		if(!$cfg['x']){
			$cfg['x'] = 0;
		}
		if(!$cfg['y']){
			$cfg['y'] = $this->obj->cfg('wm_size');
		}
		if(!$cfg['angle']){
			$cfg['angle'] = 0;
		}
		if(!$cfg['color']){
			$cfg['color'] = $this->obj->cfg('wm_color');
		}
		if(!$cfg['font']){
			$cfg['font'] = $this->obj->cfg('wm_font');
		}
		if(!$cfg['size']){
			$cfg['size'] = $this->obj->cfg('wm_size');
		}
		$this->obj->text($text, $cfg);
		return $this;
	}

	public function reorient(){
		$orientation = $this->exif('Orientation');
		switch($orientation){
			case 2:
				$this->flip(-1);
				break;
			case 3:
				$this->rotate(180);
				break;
			case 4:
				$this->rotate(180)->flip(-1);
				break;
			case 5:
				$this->rotate(270)->flip(-1);
				break;
			case 6:
				$this->rotate(270);
				break;
			case 7:
				$this->rotate(90)->flip(-1);
				break;
			case 8:
				$this->rotate(90);
				break;
		}
		return $this;
	}

	public function exif($key = null){
		$res = [];
		if(function_exists('exif_read_data')){
			$res = exif_read_data($this->obj->file);
			if(!$res) $res = [];
		}
		if($key){
			$res = $res[$key];
		}
		return $res;
	}

	public function save($to = null, $quality = 90){
		return $this->obj->save($to, $quality);
	}

	public function output($type = null){
		ob_clean();
		header('Content-type: '. $this->obj->mime);
		if($type == 'jpg') $type = 'jpeg';
		$this->obj->output($type);
	}
}
?>