<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.fileinfo.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class fileinfo{
	private $file = null;
	private $obj = null;

	public function __construct($file){
		$this->file = $file;
	}

	public function __destruct(){
		$this->obj = null;
	}

    public function md5(){
        return hash_file('md5', $this->file);
    }

    public function sha1(){
        return hash_file('sha1', $this->file);
    }

	public function mime_type(){
		return $this->file_read(FILEINFO_MIME_TYPE);
	}
	
	public function charset(){
		return $this->file_read(FILEINFO_MIME_ENCODING);
	}

	public function mime(){
		return $this->file_read(FILEINFO_MIME);
	}

	public function devices(){
		return $this->file_read(FILEINFO_DEVICES);
	}

	public function raw(){
		return $this->file_read(FILEINFO_RAW);
	}

	public function ext(){
		if(defined('FILEINFO_EXTENSION')){
			$res = $this->file_read(FILEINFO_EXTENSION);
		}
		if(!$res || $res == '???'){
			$res = substr(strrchr($this->file, '.'), 1);
		}
		return strtolower($res);
	}
	
	private function obj(){
		if(!$this->obj){
			if(class_exists('finfo', false)){
				$this->obj = new \finfo();
			}else{
				msg::set('boa.error.6', 'finfo');
			}
		}
		return $this->obj;
	}
	
	private function file_read($type = FILEINFO_NONE){
		$this->obj()->set_flags($type);
		$res = $this->obj->file($this->file);
		return $res;
	}
}
?>