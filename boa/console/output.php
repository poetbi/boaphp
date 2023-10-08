<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.console.output.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\console;

use boa\util;
use boa\base;

class output extends base{
	protected $cfg = [
		'border' => false,
		'border_h' => '-', //i.e: = + *
		'border_v' => '|',
		'wrap' => '<br>'
	];
	private $cols = [];
	private $rows = [];
	
    public function line($num = 1){
        $this->prints(str_repeat(PHP_EOL, $num));
    }

    public function printl($str, $num = 1){
		$lines = str_repeat(PHP_EOL, $num);
        $this->prints($str . $lines);
    }

    public function printw($str, $width){
		$num = $width - $this->len($str);
		if($num > 0){
			$pad = str_repeat(' ', $num);
		}
        $this->prints($str . $pad);
    }

    public function prints($str){
        fwrite(STDOUT, $str);
    }

    public function table($body, $head = [], $border = null){
		if($border !== null){
			$this->cfg['border'] = $border;
		}
		$this->calc_rows($body);
		foreach($body as $row){
			$this->calc_cols($row);
		}
		if($head){
			$this->calc_cols($head);
		}

		$max = array_sum($this->cols) + count($this->cols) + 1;
		$this->border($this->cfg['border_h'], $max);
		if($head){
			$this->draw_data($head, $max);
		}
		foreach($body as $k => $row){
			$lines = $this->rows[$k];
			$this->draw_data($row, $max, $lines);
		}
    }

	private function calc_rows($data){
		foreach($data as $row => $line){
			foreach($line as $k => $v){
				$lines = substr_count($v, $this->cfg['wrap']) + 1;
				if($lines > $this->rows[$row]){
					$this->rows[$row] = $lines;
				}
			}
		}
	}

	private function calc_cols($data){
		foreach($data as $k => $v){
			$arr = explode($this->cfg['wrap'], $v);
			foreach($arr as $line){
				$len = $this->len($line);
				if($len > $this->cols[$k]){
					$this->cols[$k] = $len;
				}
			}
		}
	}

	private function draw_data($data, $max, $rows = 1){
		if($rows > 1){
			for($j = 0; $j < $rows; $j++){
				foreach($data as $k => $v){
					$arr = explode($this->cfg['wrap'], $v);
					$this->border($this->cfg['border_v']);
					$this->printw($arr[$j], $this->cols[$k]);
				}
				$this->border($this->cfg['border_v']);
				$this->line(1);
			}
		}else{
			foreach($data as $k => $v){
				$this->border($this->cfg['border_v']);
				$this->printw($v, $this->cols[$k]);
			}
			$this->border($this->cfg['border_v']);
			$this->line(1);
		}
		$this->border($this->cfg['border_h'], $max);
	}

	private function border($type, $num = 1){
		if(!$this->cfg['border']){
			return ;
		}
		if($type == $this->cfg['border_h']){
			$this->printl(str_repeat($type, $num));
		}else{
			$this->prints(str_repeat($type, $num));
		}
	}

	private function len($str){
		if(CHARSET == 'UTF-8'){
			$times = 3;
		}else{
			$times = 2;
		}
		$len = strlen($str);
		$chars = util::len($str);
		$chars_mb = ($len - $chars) / ($times - 1);
		$chars += $chars_mb;
		return $chars;
	}
}
?>