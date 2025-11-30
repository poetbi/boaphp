<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.session.driver.file.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\session\driver;

class file{
	private $cfg = [
        'path' => BS_VAR .'session/'
    ];

	public function __construct($cfg){
        if($cfg){
			$this->cfg = array_merge($this->cfg, $cfg);
		}

		if(!file_exists($this->cfg['path'])){
			mkdir($this->cfg['path'], 0777, true);
		}
		session_save_path($this->cfg['path']);
		
		session_start();
    }
}
?>