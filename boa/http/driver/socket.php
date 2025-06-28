<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.http.driver.socket.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\http\driver;

use boa\boa;
use boa\msg;
use boa\http\driver;

class socket extends driver{
	protected $cfg = [
		'ssl' => 0, //0, 1
		'proxy' => '',
		'posttype' => 'form', //form, json, xml
		'mimetype' => 'application/x-www-form-urlencoded',
		'connect' => 15,
		'execute' => 0,
		'header' => []
	];
	private $host = null;
	private $port = 80;
	private $path = '/';
	private $fp = null;
	private $boundary = null;
	
	public function set_cookie($cookie){
		$this->cfg['header']['Cookie'] = $cookie;
	}

	public function get($url){
		$this->connect($url);
		$header = $this->header();
		
		$out  = "GET {$this->path} HTTP/1.1\r\n";
		$out .= "Host: {$this->host}:{$this->port}\r\n";
		$out .= $header;
		$out .= "Connection: close\r\n\r\n";

		$this->send($out);
	}

	public function post($url, $data){
		$this->connect($url);
		$header = $this->header();
		
		$content_type = "Content-Type: {$this->cfg['mimetype']}; charset=". CHARSET;
		
		$out  = "POST {$this->path} HTTP/1.1\r\n";
		$out .= "Host: {$this->host}:{$this->port}\r\n";
		$out .= "{$content_type}\r\n";
		$out .= "Content-Length: ". strlen($data) ."\r\n";
		$out .= $header;
		$out .= "Connection: close\r\n\r\n";
		$out .= "$data";
		
		$this->send($out);
	}
	
	public function upload($url, $file, $form){
		$this->connect($url);
		$header = $this->header();
		
		$data  = $this->form_data($form);
		$data .= $this->file_data($file);
		$data .= "--\r\n\r\n";
		
		$content_type = "Content-Type: multipart/form-data; boundary={$this->boundary}";
		
		$out  = "POST {$this->path} HTTP/1.1\r\n";
		$out .= "Host: {$this->host}:{$this->port}\r\n";
		$out .= "{$content_type}\r\n";
		$out .= "Content-Length: ". strlen($data) ."\r\n";
		$out .= $header;
		$out .= "Connection: close\r\n\r\n";
		$out .= "$data";
	
		$this->send($out);
	}
	
	private function send($data){
		$this->result = [];
		$response = '';

		if($this->cfg['execute'] > 0){
			set_time_limit($this->cfg['execute']);
			stream_set_timeout($this->fp, $this->cfg['execute']);
		}

		fwrite($this->fp, $data);
		while(!feof($this->fp)){
			$row = fread($this->fp, 128);
			$response .= $row;
		}
		$info = stream_get_meta_data($this->fp);
		fclose($this->fp);
		
		if($info['timed_out']){
			$this->result['code'] = 62;
			$this->result['msg'] = boa::lang('boa.error.62', $this->cfg['execute'] .'s');
			return false;
		}

		$response = preg_replace('/^HTTP\/([\.\w ]+?)[\t\r\n]+HTTP/', 'HTTP', $response);
		$pos = strpos($response, "\r\n\r\n");
		$this->result['head'] = substr($response, 0, $pos);
		$this->result['body'] = substr($response, $pos + 4);

		preg_match('/^HTTP\/[\d\.]+\s+(\d{3})\s+(.+?)[\r\n]/', $this->result['head'], $res);
		$this->result['code'] = $res[1];
		if($res[1] != 200){
			$this->result['msg'] = $res[2];
		}
	}
	
	private function connect($url){
		$res = $this->parse($url);
		if($this->cfg['proxy']){
			$arr = explode(':', $this->cfg['proxy']);
			$host = $arr[0];
			$port = $arr[1];
		}else{
			if($this->cfg['ssl'] && $this->port == 443){
				$host = 'ssl://'. $this->host;
			}else{
				$host = $this->host;
			}
			$port = $this->port;
		}
		
		$this->fp = fsockopen($host, $port, $errno, $errstr, $this->cfg['connect']);
		if(!$this->fp){
			msg::set('boa.error.61', "[$errno]$errstr");
		}
	}
	
	private function parse($url){
		$arr = parse_url($url);
		$this->host = $arr['host'];

		if($this->cfg['ssl'] && $arr['scheme'] == 'https'){
			$port = 443;
		}else{
			$port = $arr['port'] ? $arr['port'] : 80;
		}
		$this->port = $port;
		
		$this->path = $arr['query'] ? $arr['path'] .'?'. $arr['query'] : $arr['path'];
	}
	
	private function header(){
		$str = '';
		foreach($this->cfg['header'] as $k => $v){
			if($v){
				$str .= "$k: $v\r\n";
			}
		}
		return $str;
	}
	
	private function boundary(){
		if(!$this->boundary){
			srand((double) microtime() * 1000000);
			$key = md5(rand(1, 86400));
			$this->boundary = str_repeat('-', 10) . substr($key, 0, 25);
		}
		return $this->boundary;
	}
	
	private function form_data($form){
		$boundary = $this->boundary();
		
		$data = "--$boundary\r\n";
		foreach($form as $k => $v){
			$data .= "Content-Disposition: form-data; name=\"$k\"\r\n";
			$data .= "Content-type: text/plain; charset=". CHARSET ."\r\n\r\n";
			$data .= rawurlencode($v)."\r\n";
			$data .= "--$boundary\r\n";
		}
		return $data;
	}

	private function file_data($file){
		$boundary = $this->boundary();

		$data = '';
		foreach($file as $k => $v){
			$file_name = substr(strrchr($v[0], '/'), 1);

			$data .= "Content-Disposition: form-data; name=\"$k\"; filename=\"$file_name\"\r\n";
			$data .= "Content-Type: {$v[1]}\r\n\r\n";
			$data .= file_get_contents($v[0]) ."\r\n";
			$data .= "--$boundary\r\n";
		}
		return $data;
	}
}
?>