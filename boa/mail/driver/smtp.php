<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.mail.driver.smtp.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\mail\driver;

use boa\boa;
use boa\msg;
use boa\mail\driver;

class smtp extends driver{
	protected $cfg = [
		'smtp' => [
			'host' => '',
			'port' => 25,
			'user' => '',
			'pass' => ''
		],
		'from' => '',
		'reply' => '',
		'to' => '',
		'cc' => [],
		'bcc' => [],
		'charset' => CHARSET,
		'encode' => 'base64',
		'priority' => 3,
		'content_type' => 'text/html',
		'eol' => "\r\n",
		'conn_timeout' => 15,
		'conn_protocol' => '', //tls://, ssl://
		'header' => []
	];
	private $socket = null;

	public function send($subject, $message, $to = null){
		if($to){
			$this->cfg['to'] = $to;
		}

		if(!$this->smtp_sockopen()){
			return 82;
		}

		$this->smtp_putcmd('HELO ', 'BOA');

		if($this->cfg['smtp']['user'] && $this->cfg['smtp']['pass']){
			$res = $this->smtp_putcmd('AUTH LOGIN ', base64_encode($this->cfg['smtp']['user']));
			$res = $res && $this->smtp_putcmd('', base64_encode($this->cfg['smtp']['pass']));
			if(!$res){
				boa::log()->set('error', '[83]'. boa::lang('boa.error.83', $this->cfg['smtp']['user']));
				return 83;
			}
		}

		$res = $this->smtp_putcmd('MAIL ', 'FROM:<'. $this->cfg['from_addr'] .'>');

		$arr = array_merge([$this->cfg['to']], $this->cfg['cc'], $this->cfg['bcc']);
		foreach($arr as $v){
			list($addr, $name) = explode(' ', $v, 2);
			$res = $this->smtp_putcmd('RCPT ', "TO:<$addr>");
		}

		$this->smtp_putcmd('DATA');
		$res = fwrite($this->socket, $this->header($subject) . $this->encode($message));
		$this->smtp_putcmd('.');
		$this->smtp_putcmd('QUIT');
		fclose($this->socket);

		if(!$res){
			boa::log()->set('error', '[81]'. boa::lang('boa.error.81', $this->cfg['to']));
			return 81;
		}else{
			boa::log()->set('info', 'Mail sent('. $this->cfg['to'] .')');
			return 0;
		}
	}

	private function smtp_sockopen(){
		$this->socket = fsockopen($this->cfg['conn_protocol'] . $this->cfg['smtp']['host'], $this->cfg['smtp']['port'], $errno, $error, $this->cfg['conn_timeout']);
		if($this->socket && $this->smtp_res()){
			return true;
		}else{
			boa::log()->set('error', '[82]'. boa::lang('boa.error.82', "[$errno]$error"));
			return false;
		}
	}

	private function smtp_res(){
		$res = trim(fgets($this->socket, 512));
		return preg_match('/^[23]/', $res);
	}

	private function smtp_putcmd($cmd, $arg = ''){
		fwrite($this->socket, $cmd . $arg . $this->cfg['eol']);
		return $this->smtp_res();
	}

	private function header($subject){
		list($msec, $sec) = explode(' ', microtime());
		$mid = date('YmdHis', $sec) .'.'. ($msec * 1000000) . strstr($this->cfg['from_addr'], '@');

		$str  = 'MIME-Version: 1.0 '. $this->cfg['eol'];
		$str .= 'From: '. $this->addrs($this->cfg['from']) . $this->cfg['eol'];
		$str .= 'To: '. $this->addrs($this->cfg['to']) . $this->cfg['eol'];
		if($this->cfg['cc']){
			$str .= 'Cc: '. $this->addrs($this->cfg['cc']) . $this->cfg['eol'];
		}
		if($this->cfg['bcc']){
			$str .= 'Bcc: '. $this->addrs($this->cfg['bcc']) . $this->cfg['eol'];
		}
		if($this->cfg['reply']){
			$str .= 'Reply-to: '. $this->addrs($this->cfg['reply']) . $this->cfg['eol'];
		}
		if($this->cfg['header']){
			foreach($this->cfg['header'] as $k => $v){
				$str .= "$k: $v". $this->cfg['eol'];
			}
		}
 		$str .= 'Subject: '. $this->title($subject) . $this->cfg['eol'];
  		$str .= 'Date: '. date('r') . $this->cfg['eol'];
  		$str .= 'X-Priority: '. $this->cfg['priority'] . $this->cfg['eol'];
  		$str .= "Message-ID: $mid ". $this->cfg['eol'];
        $str .= 'Content-Type: '. $this->cfg['content_type'] .'; charset="'. $this->cfg['charset'] .'"'. $this->cfg['eol'];
        $str .= 'Content-Transfer-Encoding: '.$this->cfg['encode'] .' '. $this->cfg['eol'];
        $str .= $this->cfg['eol'] . $this->cfg['eol'];
        return $str;
    }
}
?>