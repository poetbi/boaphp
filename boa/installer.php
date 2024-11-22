<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.installer.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class installer{
	public function initlize($www = 'www', $mod = 'home'){
		$file = boa::file();
		$path = BS_BOA .'installer';

		if($www){
			mkdir(BS_ROOT ."var/$www", 0777, true);

			$dir = BS_ROOT . $www;
			$file->copy_dir("$path/www", $dir);
			mkdir("$dir/file");
			mkdir("$dir/tpl");
			$file->chmod($dir, 0777);
		}

		if($mod){
			if($mod == 'boa'){
				msg::set('boa.error.7', $mod);
			}

			$dir = BS_MOD . $mod;
			if(!file_exists($dir)){
				$file->copy_dir("$path/mod", $dir);
				
				$arr = ['cacher', 'listener', 'variable', 'language', 'model'];
				foreach($arr as $v){
					if(!file_exists("$dir/$v")) mkdir("$dir/$v");
				}

				if($mod != 'home'){
					$file->replace_dir($dir, 'home', $mod, 'php|html');
				}
				
				if($www){
					$file->copy_dir("$dir/view", BS_ROOT ."$www/tpl/$mod");
					$file->copy_dir("$dir/public", BS_ROOT ."$www/res/$mod");
				}else{
					$file->copy_dir("$dir/view", BS_WWW ."tpl/$mod");
					$file->copy_dir("$dir/public", BS_WWW ."res/$mod");
				}

				$file->chmod($dir, 0777);
			}
		}
	}

	public function install($mod){
		$program = $this->program($mod);
		return $program->install();
	}

	public function upgrade($mod){
		$program = $this->program($mod);
		return $program->upgrade();
	}

	public function uninstall($mod){
		$program = $this->program($mod);
		return $program->uninstall();
	}

	private function program($mod){
		$cls = "\\mod\\$mod\\installer\\installer";
		return new $cls();
	}
}
