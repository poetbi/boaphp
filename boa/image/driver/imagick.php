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
	public function __construct($cfg){
		parent::__construct($cfg);
		if(class_exists('Imagick', false)){
			$this->im = new \Imagick();
		}else{
			msg::set('boa.error.6', 'Imagick');
		}
	}

	protected function create_image(){
		$this->im->readImage($this->file);
	}

	public function watermark($type){
		$box = $this->get_box();
		if($box['w'] > $this->src_w * $this->cfg['wm_ratio'] || $box['h'] > $this->src_h * $this->cfg['wm_ratio']){
			return;
		}
		list($x, $y) = $this->box_pos($box);

		if($type == 1){
			$draw = new \ImagickDraw();
			$draw->setFont($this->res_path('wm_font'));
			$draw->setFontSize($this->cfg['wm_size']);
			$draw->setFillColor($this->cfg['wm_color']);
			if($this->type == IMAGETYPE_GIF){
				foreach($this->im as $frame){
					$frame->annotateImage($draw, $x, $y, 0, $this->cfg['wm_text']);
				}
			}else{
				$this->im->annotateImage($draw, $x, $y, 0, $this->cfg['wm_text']);
			}
		}else{
			$wm = new \Imagick($this->res_path('wm_logo'));
			$wm->setImageOpacity($this->cfg['wm_alpha']);
			if($this->type == IMAGETYPE_GIF){
				foreach($this->im as $frame){
					$frame->compositeImage($wm, $wm->getImageCompose(), $x, $y);
				}
			}else{
				$this->im->compositeImage($wm, $wm->getImageCompose(), $x, $y);
			}
		}
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
		$this->im->setImageAlphaChannel(\imagick::ALPHACHANNEL_DEACTIVATE);
		$dst = $this->im->mergeImageLayers(\imagick::LAYERMETHOD_FLATTEN);
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
	}

	public function save($to, $quality){
		if(!$to){
			$to = $this->file;
		}
		$this->im->setImageCompressionQuality($quality);
		$this->im->imageWriteFile(fopen($to, 'wb')); 
		$this->clear();
		return $to;
	}

	public function output($type = null){
		if($type){
			$this->im->setImageFormat($type);
		}
		echo $this->im->getImagesBlob();
	}

	private function clear(){
		if($this->im){
			$this->im->clear();
			$this->im = null;
		}
	}
}
?>