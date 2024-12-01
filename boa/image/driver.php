<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.image.driver.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\image;

use boa\util;
use boa\base;

class driver extends base{
	protected $cfg = [
		'wm_type'  => 1, //0=closed 1=text 2=logo
		'wm_ratio' => 0.4,
		'wm_margin'  => 5,
		'wm_text'  => 'boasoft.top',
		'wm_font'  => 'font.ttf',
		'wm_size'  => 13,
		'wm_color' => '#CC0000',
		'wm_logo'  => 'watermark.png',
		'wm_alpha' => 65,
		'wm_pos'   => 0, //0=random 1-9=fixed
		'tb_width'  => 320,
		'tb_height' => 200
	];
	protected $im;

	public $file = null;
	public $type = null;
	public $mime = null;
	public $src_w = 0;
	public $src_h = 0;
	public $src_x = 0;
	public $src_y = 0;
	public $dst_w = 0;
	public $dst_h = 0;
	public $dst_x = 0;
	public $dst_y = 0;

	protected function set_size($dst_w, $dst_h){
		$this->dst_w = $dst_w;
		$this->dst_h = $dst_h;
	}

	protected function reproportion(){//0=none 1=auto 2=by width 3=by height
		if($this->dst_w <= 0){
			$scale = 3;
		}else if($this->dst_h <= 0){
			$scale = 2;
		}else{
			$scale = 1;
		}

		switch($scale){
			case 1:
				$scale = ($this->src_h / $this->src_w) < ($this->dst_h / $this->dst_w) ? 2 : 3;

			case 2:
				$this->dst_h = ceil($this->dst_w * $this->src_h / $this->src_w);
				break;

			case 3:
				$this->dst_w = ceil($this->dst_h * $this->src_w / $this->src_h);
				break;
		}
	}

	protected function box_pos($box){
		$a = $this->cfg['wm_margin'];
		switch($this->cfg['wm_pos']){
			case 1 :
				$x = $y = $a;
				break;

			case 2 :
				$x = ($this->src_w - $box['w']) / 2;
				$y = $a;
				break;

			case 3 :
				$x = $this->src_w - $box['w'] - $a;
				$y = $a;
				break;

			case 4 :
				$x = $a;
				$y = ($this->src_h - $box['h']) / 2;
				break;

			case 5 :
				$x = ($this->src_w - $box['w']) / 2;
				$y = ($this->src_h - $box['h']) / 2;
				break;

			case 6 :
				$x = $this->src_w - $box['w'] - $a;
				$y = ($this->src_h - $box['h']) / 2;
				break;

			case 7 :
				$x = $a;
				$y = $this->src_h - $box['h'] - $a;
				break;

			case 8 :
				$x = ($this->src_w - $box['w']) / 2;
				$y = $this->src_h - $box['h'] - $a;
				break;

			case 9 :
				$x = $this->src_w - $box['w'] - $a;
				$y = $this->src_h - $box['h'] - $a;
				break;

			default :
				$x = mt_rand($a, $this->src_w - $box['w']);
				$y = mt_rand($a, $this->src_h - $box['h']);
		}
		return [$x, $y];
	}

	protected function rgb2hex($rgb){
		$list = ['A'=>'10', 'B'=>'11', 'C'=>'12', 'D'=>'13', 'E'=>'14', 'F'=>'15'];
		$hex = [];
		$rgb = trim(strtoupper($rgb), '# ');
		for($x = 0; $x < strlen($rgb); $x++){
			$hex[] = strtr($rgb[$x], $list);
		}
		if(count($hex) == 3){
			$hex[5] = $hex[2]; $hex[4] = $hex[2];
			$hex[3] = $hex[1]; $hex[2] = $hex[1];
			$hex[1] = $hex[0];
		}
		$arr = [
			'red'   => $hex[0] * 16 + $hex[1], 
			'green' => $hex[2] * 16 + $hex[3], 
			'blue'  => $hex[4] * 16 + $hex[5]
		];
		return $arr;
	}
	
	public function res_path($key){
		if(strpos($this->cfg[$key], '/') === false){
			return BS_VAR .'image/'. $this->cfg[$key];
		}else{
			return $this->cfg[$key];
		}
	}
}
?>