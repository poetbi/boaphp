<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.http.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class http{
	private $obj;

	public function __construct($cfg = []){
		if(!array_key_exists('driver', $cfg)) $cfg['driver'] = 'curl';

		if(isset($cfg['posttype']) && !isset($cfg['mimetype'])){
			$cfg['mimetype'] = $this->mimetype($cfg['posttype']);
		}

		$driver = '\\boa\\http\\driver\\'. $cfg['driver'];
		$this->obj = new $driver($cfg);
	}

	public function cfg($k = null, $v = null){
		return $this->obj->cfg($k, $v);
	}

	public function get_posttype(){
		return $this->obj->get_posttype();
	}

	public function get_header(){
		return $this->obj->get_header();
	}

	public function get_body($encode = null){//gzip, deflate
		$str = $this->obj->get_body();
		if($encode){
			$str = boa::archive()->decode($str, $encode);
		}
		return $str;
	}

	public function get_status(){
		return $this->obj->get_status();
	}

	public function get_error(){
		return $this->obj->get_error();
	}

	public function set_cookie($cookie){
		if(is_file($cookie)){
			$cookie = file_get_contents($cookie);
		}
		$this->obj->set_cookie($cookie);
		return $this;
	}

	public function head($url, $key = null){
		$arr = get_headers($url, 1);
		if($key === null){
			return $arr;
		}else{
			return $arr[$key];
		}
	}

	public function get($url){
		$this->obj->get($url);
		boa::log()->set('info', "[http]$url");
		return $this;
	}

	public function post($url, $data = null){
		if($data !== null){
			$type = $this->obj->get_posttype();
			if(is_array($data)){
				switch($type){
					case 'form':
						$data = http_build_query($data);
						break;

					case 'json':
						$data = json_encode($data);
						break;

					case 'xml':
						$data = boa::xml()->write($data);
						break;
				}
			}
		}
		$this->obj->cfg('mimetype', $this->mimetype($type));
		$this->obj->post($url, $data);
		boa::log()->set('info', "[http]$url");
		return $this;
	}

	public function upload($url, $file, $form = []){
		if(!is_array($file)){
			$file = ['file' => $file .';application/octet-stream'];
		}
		foreach($file as $k => $v){
			$arr = explode(';', $v, 2);
			if(count($arr) == 2){
				$path = $arr[0];
				$mime = $arr[1];
			}else{
				$path = $v;
				$mime = boa::fileinfo($v)->mime_type();
			}
			if(file_exists($path)){
				$file[$k] = [$path, $mime];
			}else{
				msg::set('boa.error.2', $path);
			}
		}
		$this->obj->upload($url, $file, $form);
		boa::log()->set('info', "[http]$url");
		return $this;
	}

	private function mimetype($posttype){
		switch($posttype){
			case 'form':
				$str = 'application/x-www-form-urlencoded';
				break;

			case 'json':
				$str = 'application/json';
				break;

			case 'xml':
				$str = 'application/xml';
				break;

			default:
				$str = 'application/octet-stream';
		}
		return $str;
	}
}
?>