<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.http.driver.curl.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\http\driver;

use boa\boa;
use boa\msg;
use boa\http\driver;

class curl extends driver{
	protected $cfg = [
		'ssl' => 0, //0, 1, 2
		'proxy' => '',
		'posttype' => 'form', //form, json, xml
		'mimetype' => 'application/x-www-form-urlencoded',
		'timeout_connect' => 15,
		'timeout_execute' => 0,
		'header' => [],
		'option' => []
	];
	private $option = [];
	private $ch;

	public function __construct($cfg){
		parent::__construct($cfg);

		if(!function_exists('curl_exec')){
			msg::set('boa.error.6', 'CURL');
		}
		
		$this->ch = curl_init();
		$this->option_init();
	}
	
	public function __destruct(){
		curl_close($this->ch);
	}

	public function set_cookie($cookie){
		curl_setopt($this->ch, CURLOPT_COOKIE, $cookie);
	}
	
	public function get($url){
		$this->option_cfg();
		$this->option[CURLOPT_URL] = $url;
		$this->send();
	}
	
	public function post($url, $data){
		$this->option_cfg();
		$this->option[CURLOPT_URL] = $url;
		$this->option[CURLOPT_POST] = true;
		$this->option[CURLOPT_POSTFIELDS] = $data;
		$this->send();
	}

	public function upload($url, $file, $form){
		$this->option_cfg();
		$file = $this->file_data($file);
		$data = array_merge($form, $file);
		$this->option[CURLOPT_URL] = $url;
		$this->option[CURLOPT_POST] = true;
		$this->option[CURLOPT_POSTFIELDS] = $data;
		$this->send();
	}
	
	private function send(){
		$this->result = [];

		if($this->cfg['timeout_execute'] > 0){
			set_time_limit($this->cfg['timeout_execute']);
		}

		curl_setopt_array($this->ch, $this->option);
		$response = curl_exec($this->ch);
		$errno = curl_errno($this->ch);
		$error = curl_error($this->ch);
		if($errno){
			$this->result['code'] = 63;
			$this->result['msg'] = boa::lang('boa.error.63', "[$errno]$error");
			return false;
		}

		$response = preg_replace('/^HTTP\/([\.\w ]+?)[\t\r\n]+HTTP/', 'HTTP', $response);
		$arr = explode("\r\n\r\n", $response, 2);
		$this->result['head'] = trim($arr[0]);
		$this->result['body'] = $arr[1];

		preg_match('/^HTTP\/[\d\.]+ (\d{3})/', $this->result['head'], $res);
		$this->result['code'] = $res[1];
		if($res[1] != 200){
			$this->result['msg'] = $res[2];
		}
	}
	
	private function option_cfg(){
		$this->option = $this->cfg['option'];

		if($this->cfg['posttype'] != 'form'){
			$this->cfg['header']['Content-type'] = $this->cfg['mimetype'] .'; charset='. CHARSET;
		}

		$arr = [];
		foreach($this->cfg['header'] as $k => $v){
			$arr[] = "$k: $v";
		}

		if($arr){
			$this->option[CURLOPT_HTTPHEADER] = $arr;
		}

		if($this->cfg['proxy']){
			$this->option[CURLOPT_PROXY] = $this->cfg['proxy'];
		}

		if($this->cfg['timeout_connect'] > 0){
			$this->option[CURLOPT_CONNECTTIMEOUT] = $this->cfg['timeout_connect'];
		}

		if($this->cfg['timeout_execute'] > 0){
			$this->option[CURLOPT_TIMEOUT] = $this->cfg['timeout_execute'];
		}
		
		$this->option_ssl();
	}

	private function option_ssl(){
		if($this->cfg['ssl'] > 0){
			$this->option[CURLOPT_SSL_VERIFYPEER] = 1;
			$this->option[CURLOPT_SSL_VERIFYHOST] = 2;

			$curl = BS_VAR .'http/';
			if($this->cfg['ssl'] == 2){
				if(!$this->option[CURLOPT_SSLCERT] && file_exists($curl .'two/cacert.pem')){
					$this->option[CURLOPT_SSLCERT] = $curl .'two/cacert.pem';
				}
				$this->option[CURLOPT_SSLCERTTYPE] = 'PEM';

				if(!$this->option[CURLOPT_SSLKEY] && file_exists($curl .'two/cacert.key')){
					$this->option[CURLOPT_SSLKEY] = $curl .'two/cacert.key';
				}
				$this->option[CURLOPT_SSLKEYTYPE] = 'PEM';
			}else{
				if(!$this->option[CURLOPT_CAINFO] && file_exists($curl .'one/cacert.pem')){
					$this->option[CURLOPT_CAINFO] = $curl .'one/cacert.pem';
				}
			}
		}
	}

	private function option_init(){
		$arr[CURLOPT_RETURNTRANSFER] = true;
		$arr[CURLOPT_HEADER] = true;

		if(defined('DEBUG') && DEBUG){
			$arr[CURLOPT_VERBOSE] = true;
			$arr[CURLOPT_CERTINFO] = true;
			$arr[CURLOPT_STDERR] = fopen(BS_VAR .'http.curl.txt', 'a');
		}
		curl_setopt_array($this->ch, $arr);
	}
	
	private function file_data($file){
		if(class_exists('CURLFile', false)){ //php 5.5+
			foreach($file as $k => $v){
				$file_name = substr(strrchr($v[0], '/'), 1);
				$file[$k] = new \CURLFile($v[0], $v[1], $file_name);
			}

			if(defined('CURLOPT_SAFE_UPLOAD')){ //php 7
				$this->option[CURLOPT_SAFE_UPLOAD] = true;
			}
		}else{
			foreach($file as $k => $v){
				$file[$k] = "@{$v[0]};type={$v[1]}";
			}
		}

		return $file;
	}
}
?>