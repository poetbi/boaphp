<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.mail.driver.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\mail;

use boa\base;

class driver extends base{	
	public function anti_spam($header){
		if($header){
			foreach($header as $k => $v){
				$this->cfg['header'][$k] = $v;
			}
		}
	}

	protected function addrs($addrs){
		if(is_array($addrs)){
			foreach($addrs as $k => $v){
				list($addr, $name) = explode(' ', $v, 2);
				$arr[$k] = $this->addr($addr, $name);
			}
			$str = implode(',', $arr);
		}else{
			list($addr, $name) = explode(' ', $addrs, 2);
			$str = $this->addr($addr, $name);
		}
		return $str;
	}

	protected function addr($addr, $name = null){
		if($name !== null){
			$name = trim($name);
			if($name){
				$name = $this->title($name);
				$addr = "$name <$addr>";
			}
		}
		return $addr;
	}

	protected function title($str){
		return '=?'. $this->cfg['charset'] .'?B?'. base64_encode($str) .'?=';
	}

	protected function encode($str){
		switch($this->cfg['encode']){
			case 'base64':
				$str = base64_encode($str);
				break;

			case 'quoted-printable':
				$str = quoted_printable_encode($str);
				break;
		}
		$str = chunk_split($str, 76, $this->cfg['eol']);
		return $str;
	}
}
?>