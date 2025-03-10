<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.archive.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class archive{
	private $obj;

	public function __construct($cfg = []){
		if(!array_key_exists('driver', $cfg)) $cfg['driver'] = 'zip';
		
		$this->cfg = $cfg;
	}
	
	public function cfg($k = null, $v = null){
		return $this->obj()->cfg($k, $v);
	}

	public function compress($source, $dest){
		if(!file_exists($source)){
			msg::set('boa.error.2', $source);
		}

		$source = $this->format($source, true);
		$dest = $this->format($dest);
		$res = $this->obj()->compress($source, $dest);
		$this->obj->close();

		if($res === false){
			msg::set('boa.error.153', $dest);
		}else{
			$res = true;
		}
        return $res;
    }

	public function decompress($source, $dest){
		if(!file_exists($source)){
			msg::set('boa.error.2', $source);
		}

		$source = $this->format($source);
		$dest = $this->format($dest, true);
		$res = $this->obj()->decompress($source, $dest);
		$this->obj->close();

		if($res === false){
			msg::set('boa.error.154', $source);
		}else{
			$res = true;
		}
        return $res;
    }

	public function obj(){
		if(!$this->obj){
			$driver = '\\boa\\archive\\driver\\'. $this->cfg['driver'];
			$this->obj = new $driver($this->cfg);
		}
		return $this->obj;
	}

	private function format($path, $isdir = false){
		$path = str_replace('\\', '/', $path);
		if($isdir) $path = rtrim($path, '/') .'/';
		return $path;
	}
	
	public function encode($data, $type = 'zlib', $level = -1){
		$coding = $this->coding($type);
		switch($coding){
			case 'lzf':
				if(function_exists('lzf_compress')){
					$res = lzf_compress($data);
				}else{
					msg::set('boa.error.6', 'lzf_compress()');
				}
				break;

			case 'bzip2':
				if(function_exists('bzcompress')){
					if(level <= 0) $level = 4;
					$res = bzcompress($data, $level);
					if(is_int($res)){
						$res = false;
					}
				}else{
					msg::set('boa.error.6', 'bzcompress()');
				}
				break;

			default:
				$res = zlib_encode($data, $coding, $level);
		}

		if($res === false){
			msg::set('boa.error.151', $type);
		}
        return $res;
    }

	public function decode($data, $type = 'zlib'){
		$coding = $this->coding($type);
		switch($coding){
			case 'lzf':
				$res = lzf_decompress($data);
				break;

			case 'bzip2':
				$res = bzdecompress($data);
				if(is_int($res)) $res = false;
				break;

			default:
				$res = zlib_decode($data);
		}

		if($res === false){
			msg::set('boa.error.152', $type);
		}
        return $res;
    }

	private function coding($type){
		switch($type){
			case 'raw':
				$res = ZLIB_ENCODING_RAW;
				break;

			case 'zlib':
				$res = ZLIB_ENCODING_GZIP;
				break;

			case 'deflate':
				$res = ZLIB_ENCODING_DEFLATE;
				break;

			default:
				$res = $type;
		}
		return $res;
	}
}
?>