<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.mail.driver.mail.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\mail\driver;

use boa\boa;
use boa\msg;
use boa\mail\driver;

class mail extends driver{
	protected $cfg = [
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
		'header' => []
	];

	public function send($subject, $message, $to = null){
		if($to){
			$this->cfg['to'] = $to;
		}

		$header = $this->header();

		if($this->cfg['from']){
			$param = '-f'. $this->cfg['from_addr'];
			if($this->cfg['from_name']){
				$param .= ' -F'. $this->cfg['from_name'];
			}
		}

		$res = mail($this->cfg['to'], $subject, $message, $header, $param);

		if(!$res){
			boa::log()->set('error', '[81]'. boa::lang('boa.error.81', $this->cfg['to']));
			return 81;
		}else{
			boa::log()->set('info', 'Mail sent('. $this->cfg['to'] .')');
			return 0;
		}
	}

	private function header(){
		$str  = 'MIME-Version: 1.0 '. $this->cfg['eol'];
		$str .= 'To: '. $this->addrs($this->cfg['to']) . $this->cfg['eol'];
		if($this->cfg['from']){
			$str .= 'From: '. $this->addrs($this->cfg['from']) . $this->cfg['eol'];
		}
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
   		$str .= 'X-Priority: '. $this->cfg['priority'] . $this->cfg['eol'];
        $str .= 'Content-Type: '. $this->cfg['content_type'] .'; charset="'. $this->cfg['charset'] .'"'. $this->cfg['eol'];
        $str .= 'Content-Transfer-Encoding: '.$this->cfg['encode'] . $this->cfg['eol'];
		return $str;
	}
}
?>