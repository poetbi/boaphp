<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.image.driver.gd.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\image\driver;

use boa\boa;
use boa\msg;
use boa\image\driver;

class gd extends driver{
	protected function create_image(){
		$this->clear();
		$create = $this->fun_create($this->type);
		$this->im = $create($this->file);
	}

	public function watermark($type){
		if($this->type == IMAGETYPE_GIF){
			$str = file_get_contents($this->file);
			$ani = strpos($str, chr(0x21).chr(0xFF).chr(0x0B).'NETSCAPE2.0') === false ? 0 : 1;
			if($ani){
				return;
			}
		}

		$box = $this->get_box();
		if($box['w'] > $this->src_w * $this->cfg['wm_ratio'] || $box['h'] > $this->src_h * $this->cfg['wm_ratio']){
			return;
		}
		list($x, $y) = $this->box_pos($box);

		if($type == 1){
			$arr = $this->rgb2hex($this->cfg['wm_color']);
			$color = imagecolorallocate($this->im, $arr['red'], $arr['green'], $arr['blue']);
			$font = $this->res_path('wm_font');
			if(file_exists($font)){
				imagettftext($this->im, $this->cfg['wm_size'], 0, $x, $y + $box['h'], $color, $font, $this->cfg['wm_text']);
			}else{
				imagestring($this->im, 5, $x, $y, $this->cfg['wm_text'], $color);
			}
		}else{
			$logo = $this->res_path('wm_logo');
			if(file_exists($logo)){
				$create = $this->fun_create($box['t']);
				$imi = $create($logo);
				imagealphablending($this->im, true);
				imagecopymerge($this->im, $imi, $x, $y, 0, 0, $box['w'], $box['h'], $this->cfg['wm_alpha']);
				imagedestroy($imi);
			}else{
				msg::set('boa.error.2', $logo);
			}
		}
	}

	public function scale($dst_w, $dst_h){
		$this->set_size($dst_w, $dst_h);
		$this->reproportion();
		if(function_exists('imagescale')){
			$dst = imagescale($this->im, $this->dst_w, $this->dst_h);
			imagedestroy($this->im);
			$this->im = $dst;
		}else{
			$this->process();
		}
	}

	public function crop($dst_w, $dst_h, $src_x, $src_y){
		if(function_exists('imagecrop')){
			$dst = imagecrop($this->im, ['x' => $src_x, 'y' => $src_y, 'width' => $dst_w, 'height' => $dst_h]);
			imagedestroy($this->im);
			$this->im = $dst;
		}else{
			$this->set_size($dst_w, $dst_h);
			$this->src_x = $src_x;
			$this->src_y = $src_y;
			$this->process();
			$this->src_x = 0;
			$this->src_y = 0;
		}
	}

	public function rotate($angle, $bgcolor){
		$arr = $this->rgb2hex($bgcolor);
		$color = imagecolorallocate($this->im, $arr['red'], $arr['green'], $arr['blue']);
		$dst = imagerotate($this->im, $angle, $color);

		imagedestroy($this->im);
		$this->im = $dst;
	}

	public function flatten($bgcolor){
		$arr = $this->rgb2hex($bgcolor);
		$dst = imagecreatetruecolor($this->src_w, $this->src_h);
		$color = imagecolorallocate($dst, $arr['red'], $arr['green'], $arr['blue']);

		imagefilledrectangle($dst, 0, 0, $this->src_w, $this->src_h, $color);
		imagecopy($dst, $this->im, 0, 0, 0, 0, $this->src_w, $this->src_h);

		imagedestroy($this->im);
		$this->im = $dst;
	}
	
	public function flip($mode){
		if(function_exists('imageflip')){
			switch($mode){
				case -1:
					$mode = IMG_FLIP_HORIZONTAL;
					break;

				case 1:
					$mode = IMG_FLIP_VERTICAL;
					break;

				default:
					$mode = IMG_FLIP_BOTH;
			}
			imageflip($this->im, $mode);
		}else{
			switch($mode){
				case -1:
					$this->flip_h();
					break;

				case 1:
					$this->flip_v();
					break;

				default:
					$this->flip_h();
					$this->flip_v();
			}
		}
	}

	public function text($text, $cfg){
		$arr = $this->rgb2hex($cfg['color']);
		$color = imagecolorallocate($this->im, $arr['red'], $arr['green'], $arr['blue']);
		imagettftext($this->im, $cfg['size'], $cfg['angle'], $cfg['x'], $cfg['y'], $color, $cfg['font'], $text);
	}

	public function save($to, $quality){
		if(!$to){
			$to = $this->file;
		}
		$ext = strtolower(substr(strrchr($to, '.'), 1));
		$write = $this->fun_write_ext($ext);
		if($ext == 'jpg'){
			$write($this->im, $to, $quality);
		}else{
			$write($this->im, $to);
		}
		$this->clear();
		return $to;
	}

	public function output($type = null){
		if(!$type){
			switch($this->type){
				case IMAGETYPE_GIF:
					$type = 'gif';
					break;

				case IMAGETYPE_JPEG:
					$type = 'jpeg';
					break;

				case IMAGETYPE_PNG:
					$type = 'png';
					break;
			}
		}
		$func = "image$type";
		$func($this->im);
	}

	private function process(){
		$dst = imagecreatetruecolor($this->dst_w, $this->dst_h);

		if($this->type === IMAGETYPE_PNG){
			imagealphablending($dst, false);
			imagesavealpha($dst, true);
		}

		imagecopyresampled($dst, $this->im, $this->dst_x, $this->dst_y, $this->src_x, $this->src_y, $this->dst_w, $this->dst_h, $this->src_w, $this->src_h);

		imagedestroy($this->im);
		$this->im = $dst;
	}

	private function clear(){
		if($this->im){
			imagedestroy($this->im);
			$this->im = null;
		}
	}
	
	private function flip_h(){
		for($y = 0; $y < $this->src_h; $y++){
			$x_left  = 0;
			$x_right = $this->src_w - 1;

			while($x_left < $x_right){
				$cl = imagecolorat($this->im, $x_left, $y);
				$cr = imagecolorat($this->im, $x_right, $y);

				imagesetpixel($this->im, $x_left, $y, $cr);
				imagesetpixel($this->im, $x_right, $y, $cl);

				$x_left++;
				$x_right--;
			}
		}
	}

	private function flip_v(){
		for($x = 0; $x < $this->src_w; $x++){
			$y_top    = 0;
			$y_bottom = $this->src_h - 1;

			while($y_top < $y_bottom){
				$ct = imagecolorat($this->im, $x, $y_top);
				$cb = imagecolorat($this->im, $x, $y_bottom);

				imagesetpixel($this->im, $x, $y_top, $cb);
				imagesetpixel($this->im, $x, $y_bottom, $ct);

				$y_top++;
				$y_bottom--;
			}
		}
	}

	private function fun_create($type){
		switch($type){
			case IMAGETYPE_GIF : $fun = 'imagecreatefromgif'; break;
			case IMAGETYPE_JPEG: $fun = 'imagecreatefromjpeg'; break;
			case IMAGETYPE_PNG : $fun = 'imagecreatefrompng'; break;
		}
		return $fun;
	}

	private function fun_write_ext($ext){
		switch($ext){
			case 'gif': $fun = 'imagegif'; break;
			case 'jpg': $fun = 'imagejpeg'; break;
			case 'png': $fun = 'imagepng'; break;
		}
		return $fun;
	}
}
?>