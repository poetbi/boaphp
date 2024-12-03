<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.image.driver.imagick.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\image\driver;

use boa\boa;
use boa\msg;
use boa\image\driver;

class imagick extends driver{
	private $box;

	public function __construct($cfg){
		parent::__construct($cfg);
		if(class_exists('Imagick', false)){
			$this->im = new \Imagick();
		}else{
			msg::set('boa.error.6', 'Imagick');
		}
	}

	public function open($img, $type = 0){
		$this->clear();
		if($type){
			$res = $this->im->readImageBlob($img);
		}else{
			$this->file = $img;
			$res = $this->im->readImage($img);
		}
		if(!$res) msg::set('boa.error.161', $this->file);

		$this->src_w = $this->im->getImageWidth();
		$this->src_h = $this->im->getImageHeight();
		$this->mime = $this->im->getImageMimeType();
	}

	public function watermark($type){
		$box = $this->get_box($type);
		if($box['w'] > $this->src_w * $this->cfg['wm_ratio'] || $box['h'] > $this->src_h * $this->cfg['wm_ratio']){
			return;
		}
		list($x, $y) = $this->box_pos($box);

		$num = $this->im->getNumberImages();
		if($type == 1){
			for($i = 0; $i < $num; $i++){
				$this->im->setIteratorIndex($i);
				$this->im->annotateImage($this->box, $x, $y + $box['h'] + $box['y'], 0, $this->cfg['wm_text']);
			}
		}else{
			$op = $this->box->getImageCompose();
			for($i = 0; $i < $num; $i++){
				$this->im->setIteratorIndex($i);
				$this->im->compositeImage($this->box, $op, $x, $y);
			}
		}
		$this->box->clear();
		$this->box->destroy();
	}

	public function scale($dst_w, $dst_h){
		$this->set_size($dst_w, $dst_h);
		$this->reproportion();
		$this->im->scaleImage($this->dst_w, $this->dst_h, true);
	}

	public function crop($dst_w, $dst_h, $src_x, $src_y){
		$this->im->cropImage($dst_w, $dst_h, $src_x, $src_y);
	}

	public function rotate($angle, $bgcolor){
		$this->im->rotateImage($bgcolor, $angle);
	}

	public function flatten($bgcolor){
		$this->im->setImageBackgroundColor($bgcolor);
		$this->im->setImageAlphaChannel(\Imagick::ALPHACHANNEL_DEACTIVATE);
		$dst = $this->im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
		$this->clear();
		$this->im = $dst;
	}

	public function flip($mode){
		switch($mode){
			case -1:
				$this->im->flopImage();
				break;

			case 1:
				$this->im->flipImage();
				break;

			default:
				$this->im->flopImage();
				$this->im->flipImage();
		}
	}

	public function text($text, $cfg){
		$draw = new \ImagickDraw();
        $draw->setFillColor($cfg['color']);
        $draw->setFont($cfg['font']);
        $draw->setFontSize($cfg['size']);
		$this->im->annotateImage($draw, $cfg['x'], $cfg['y'], $cfg['angle'], $text);
		$draw->clear();
		$draw->destroy();
	}

	public function save($to, $quality){
		if(!$to) $to = $this->file;
		$this->im->setImageCompressionQuality($quality);
		$this->im->writeImages($to, true); 
		return $to;
	}

	public function output($type){
		echo $this->im->getImagesBlob();
	}

	public function clear(){
		if($this->im){
			$this->im->clear();
			$this->im->destroy();
			$this->im = null;
		}
	}

	private function get_box($type){
		if($type == 1){
			$this->box = new \ImagickDraw();
			$this->box->setTextEncoding(CHARSET);
			$this->box->setFont($this->res_path('wm_font'));
			$this->box->setFontSize($this->cfg['wm_size']);
			$this->box->setFillColor($this->cfg['wm_color']);
			$arr = $this->im->queryFontMetrics($this->box, $this->cfg['wm_text']);
			$box_w = $arr['textWidth'];
			$box_h = $arr['textHeight'];
			$box_y = $arr['descender'];
		}else{
			$this->box = new \Imagick($this->res_path('wm_logo'));
			$this->box->setImageAlphaChannel(\Imagick::ALPHACHANNEL_OPAQUE);
			$this->box->evaluateImage(\Imagick::EVALUATE_MULTIPLY, $this->cfg['wm_alpha'] / 100, \Imagick::CHANNEL_ALPHA);
			$box_w = $this->box->getImageWidth();
			$box_h = $this->box->getImageHeight();
		}
		$box = [
			'w' => $box_w,
			'h' => $box_h,
			'y' => $box_y
		];
		return $box;
	}
}
?>