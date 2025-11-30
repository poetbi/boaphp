<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.file.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class file extends base{
	protected $cfg = [
		'mode' => 0777,
		'safe_mode' => false,
		'safe_path' => BS_WWW
	];
	const TYPE_FOLDER = 1;
	const TYPE_FILE = 2;

	public function read($file, $maxlen = 0, $offset = 0){
		if($maxlen > 0){
			return file_get_contents($file, false, null, $offset, $maxlen);
		}else{
			return file_get_contents($file, false, null, $offset);
		}
	}

	public function write($file, $str, $append = false){
		$path = dirname($file);
		if($path && !file_exists($path)){
			mkdir($path, $this->cfg['mode'], true);
		}
		$flag = $append ? FILE_APPEND : 0;
		return file_put_contents($file, $str, $flag);
	}

	public function chmod($path, $mode = 0777){
		$fp = opendir($path);
		if($fp){
			while(false !== ($v = readdir($fp))){
			   if($v != '.' && $v != '..'){
					$thispath = "$path/$v";
					if(is_dir($thispath)){
						$this->chmod($thispath, $mode);
					}else{
						chmod($thispath, $mode);
					}
				}
			}
			closedir($fp);
		}else{
			return false;
		}
		return chmod($path, $mode);
	}

	public function realpath($path, $root = null){
		if(substr($path, 0, 1) === '.'){
			if($root === null){
				$root = BS_ROOT;
			}else{
				$root = rtrim($root, '/') .'/';
			}
			$path = $root . $path;
		}
		$path = preg_replace('/[\/\\\]+/', '/', $path);
		$path = preg_replace('/((?<=\/)\.\/)+/', '', $path);

		if(strpos($path, '../') !== false){
			$dir = strstr($path, '../');
			$path = strstr($path, '../', true);
			$arr = explode('/', $dir);
			foreach($arr as $v){
				if($v == '..'){
					$path = preg_replace('/[^\/]+\/$/', '', $path);
				}else{
					break;
				}
			}
			$path .= str_replace('../', '', $dir);
		}

		return $path;
	}

	public function file2url($path, $host = null){
		$root = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
		$url = preg_replace('/^'. preg_quote($root, '/') .'/', '', $path);
		if($host){
			$url = $host . $url;
		}
		return $url;
	}
	
	public function read_dir($path, $type = 0){
		if(!$path || !file_exists($path)){
			return false;
		}else{
			$path = rtrim($path, '/');
			$path = $this->check($path);
		}

		$arr = [];
		if($fp = opendir($path)){
			while(false !== ($v = readdir($fp))){
			   if($v != '.' && $v != '..'){
					$thispath = "$path/$v";
					switch($type){
						case self::TYPE_FOLDER:
							if(is_dir($thispath)){
								$arr[] = $v;
							}
							break;

						case self::TYPE_FILE:
							if(is_file($thispath)){
								$arr[] = $v;
							}
							break;

						default:
							$arr[] = $v;
					}
				}
			}
			closedir($fp);
		}else{
			return false;
		}
		return $arr;
	}

	public function copy_dir($source, $dest, $override = false){
		if(!$source || !file_exists($source)){
			return false;
		}else{
			$source = rtrim($source, '/');
		}

		$dest = rtrim($dest, '/');
		$dest = $this->check($dest);

		$res = $this->do_copy_dir($source, $dest, $override);
		return $res;
	}

	public function clear_dir($path, $deldir = false){
		if(!$path || !file_exists($path)){
			return false;
		}else{
			$path = rtrim($path, '/');
			$path = $this->check($path);
		}

		$res = $this->do_clear_dir($path, $deldir);
		return $res;
	}

	public function count_dir($path, $recursive = false){
		if(!$path || !file_exists($path)){
			return false;
		}else{
			$path = rtrim($path, '/');
		}

		$arr = $this->do_count_dir($path, $recursive);
		return $arr;
	}

	public function replace_dir($path, $search, $repalce = '', $exts = null){
		$search = '/'. preg_quote($search, '/') .'/';
		if($exts){
			$exts = str_replace(' ', '', $exts);			
			$filter = "/\.($exts)$/i";
		}
		$res = $this->preg_replace_dir($path, $search, $repalce, $filter);
		return $res;
	}

	public function preg_replace_dir($path, $search, $repalce = '', $filter = null){
		if(!$path || !file_exists($path)){
			return false;
		}else{
			$path = rtrim($path, '/');
			$path = $this->check($path);
		}

		$res = $this->do_replace_dir($path, $search, $repalce, $filter);
		return $res;
	}

	private function check($path){
		if($this->cfg['safe_mode']){
			$path = $this->realpath($path);
			if(strpos($path, $this->cfg['safe_path']) === 0){
				return $path;
			}else{
				msg::set('boa.error.51', $path);
			}
		}else{
			return $path;
		}
	}

	private function do_copy_dir($source, $dest, $override){
		if(!file_exists($dest)){
			$res = mkdir($dest, $this->cfg['mode'], true);
			if($res === false){
				return false;
			}
		}

		$fp = opendir($source);
		if($fp){
			while(false !== ($v = readdir($fp))){
			   if($v != '.' && $v != '..'){
					$thispath = "$source/$v";
					$thatpath = "$dest/$v";
					if(is_dir($thispath)){
						$this->do_copy_dir($thispath, $thatpath, $override);
					}else{
						if($override){
							copy($thispath, $thatpath);
						}else{
							if(!file_exists($thatpath)){
								copy($thispath, $thatpath);
							}
						}
					}
				}
			}
			closedir($fp);
		}else{
			return false;
		}

		return true;
	}

	private function do_clear_dir($path, $deldir){
		$fp = opendir($path);
		if($fp){
			while(false !== ($v = readdir($fp))){
			   if($v != '.' && $v != '..'){
					$thispath = "$path/$v";
					if(is_dir($thispath)){
						$this->do_clear_dir($thispath, true);
					}else{
						@unlink($thispath);
					}
				}
			}
			closedir($fp);
		}else{
			return false;
		}

		if($deldir){
			@rmdir($path);
		}

		return true;
	}

	private function do_count_dir($path, $recursive){
		$arr = [
			'bytes' => 0,
			'files' => 0,
			'folders' => 0
		];

		$fp = opendir($path);
		if($fp){
			while(false !== ($v = readdir($fp))){
			   if($v != '.' && $v != '..'){
					$thispath = "$path/$v";
					if(is_dir($thispath)){
						$arr['folders'] += 1;

						if($recursive){
							$sub = $this->do_count_dir($thispath, true);
							$arr['bytes'] += $sub['bytes'];
							$arr['files'] += $sub['files'];
							$arr['folders'] += $sub['folders'];
						}
					}else{
						$arr['bytes'] += filesize($thispath);
						$arr['files'] += 1;
					}
				}
			}
			closedir($fp);
		}else{
			return false;
		}

		return $arr;
	}

	private function do_replace_dir($path, $search, $repalce, $filter){
		$fp = opendir($path);
		if($fp){
			while(false !== ($v = readdir($fp))){
			   if($v != '.' && $v != '..'){
					$thispath = "$path/$v";
					if(is_dir($thispath)){
						$this->do_replace_dir($thispath, $search, $repalce, $filter);
					}else{
						$check = true;
						if($filter){
							$check = preg_match($filter, $v);
						}
						if($check){
							$str = file_get_contents($thispath);
							$str = preg_replace($search, $repalce, $str);
							$this->write($thispath, $str);
						}
					}
				}
			}
			closedir($fp);
		}else{
			return false;
		}
		return true;
	}
}
?>