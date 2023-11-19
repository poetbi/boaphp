<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.archive.driver.zip.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\archive\driver;

use boa\boa;
use boa\msg;
use boa\base;

class zip extends base{
	protected $cfg = [
		'password' => null,
		'comment' => '',
		'filter' => null,
		'charset' => 'GBK'
	];
	private $obj = null;
	private $root;
	
	public function __construct(){
		if(!class_exists('ZipArchive', false)){
			msg::set('boa.error.6', 'zip');
		}
	}

	public function compress($source, $dest){
		$this->open($dest, \ZipArchive::OVERWRITE | \ZipArchive::CREATE);
		$this->root = $source;
		$res = $this->do_compress($source);
		if($this->cfg['comment']){
			if($this->cfg['charset']){
				$this->fix_comment($dest);
			}else{
				$this->obj->setArchiveComment($this->cfg['comment']);
			}
		}
		return $res;
	}

	public function decompress($source, $dest){
		$this->open($source);
		$file = boa::file();
		$i = 0;
		$res = true;
		while($name = $this->obj->getNameIndex($i)){
			$path = $dest . $name;
			$str = $this->obj->getFromName($name);
			if(substr($name, -1) == '/' && strlen($str) == 0){
				$res = $res && mkdir($path, 0777, true);
			}else{
				$res = $res && $file->write($path, $str);
			}
			$i++;
		}
		return $res;
	}

	public function open($file, $flag = null){
		if(!$this->obj){
			$this->obj = new \ZipArchive();
		}
		$res = $this->obj->open($file, $flag);
		if($res === true){
			if($this->cfg['password']){
				$this->obj->setPassword($this->cfg['password']);
			}
		}else{
			$key = boa::getkey($res, 1, 'ZipArchive', 'ER_');
			msg::set('boa.error.157', $file, 'ZipArchive::'. $key);
		}
	}

	public function close(){
		if($this->obj){
			$this->obj->close();
			$this->obj = null;
		}
	}

	private function fix_comment($dest){
		$this->close();
		$str = \boa\util::convert($this->cfg['comment'], CHARSET, $this->cfg['charset']);
		$fh = fopen($dest, 'r+b');
		fseek($fh, -2, SEEK_END);
		$str = pack('v', strlen($str)) . $str;
		fwrite($fh, $str);
		fclose($fh);
	}
	
	private function do_compress($path){
		if($fp = opendir($path)){
			while(false !== ($v = readdir($fp))){
				if($v != '.' && $v != '..'){
					if($this->cfg['filter']){
						if(!preg_match($this->cfg['filter'], $v)) continue;
					}

					$long = $path . $v;
					$short = preg_replace('/^'. preg_quote($this->root, '/') .'/', '', $long);
					if(is_dir($long)){
						$this->obj->addEmptyDir($short);
						$res = $this->do_compress($long .'/');
					}else{
						$res = $this->obj->addFile($long, $short);
						if($this->cfg['password']){
							if(method_exists($this->obj, 'setEncryptionName')){
								$this->obj->setEncryptionName($short, \ZipArchive::EM_AES_256, $this->cfg['password']);
							}else{
								msg::set('boa.error.6', 'setEncryptionName() [php 7.2+]');
							}
						}
					}
				}
			}
			closedir($fp);
		}
		return $res;
	}
}
?>