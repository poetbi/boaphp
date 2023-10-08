<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.crypt.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class crypt extends base{
	protected $cfg = [
		'cipher' => 'aes-128-cbc',
		'key' => '',
		'options' => 0,
		'iv' => null,
		'pubkey' => '', //BS_VAR .'crypt/pubkey.pem'
		'prikey' => '', //BS_VAR .'crypt/prikey.pem'
		'pripass' => '',
		'sign_alg' => 'sha1'
	];

	public function __construct($cfg = []){
        parent::__construct($cfg);
		
		if(!function_exists('openssl_encrypt')){
			msg::set('boa.error.6', 'OpenSSL');
		}

		$crypt = BS_VAR .'crypt/';
		if(!$this->cfg['pubkey']){
			$this->cfg['pubkey'] = $crypt .'pubkey.pem';
		}
		if(!$this->cfg['prikey']){
			$this->cfg['prikey'] = $crypt .'prikey.pem';
		}
	}

	public function enc($data, $cipher = null, $key = null, $options = null, $iv = null){
		if($cipher === null) $cipher = $this->cfg['cipher'];
		if($key === null) $key = $this->cfg['key'];
		if($options === null) $options = $this->cfg['options'];
		if($iv === null) $iv = $this->cfg['iv'];

		if(in_array($cipher, openssl_get_cipher_methods())){
			$data = openssl_encrypt($data, $cipher, $key, $options, $iv);
		}else{
			msg::set('boa.error.141', $cipher);
		}
		return $data;
	}

	public function dec($data, $cipher = null, $key = null, $options = null, $iv = null){
		if($cipher === null) $cipher = $this->cfg['cipher'];
		if($key === null) $key = $this->cfg['key'];
		if($options === null) $options = $this->cfg['options'];
		if($iv === null) $iv = $this->cfg['iv'];

		if(in_array($cipher, openssl_get_cipher_methods())){
			$data = openssl_decrypt($data, $cipher, $key, $options, $iv);
		}else{
			msg::set('boa.error.141', $cipher);
		}
		return $data;
	}

	public function public_enc($data, $padding = OPENSSL_PKCS1_PADDING){
		$key = $this->get_public_key();
		$res = openssl_public_encrypt($data, $result, $key, $padding);

		if($res === true){
			return $result;
		}else{
			msg::set('boa.error.143');
		}
	}

	public function public_dec($data, $padding = OPENSSL_PKCS1_PADDING){
		$key = $this->get_public_key();
		$res = openssl_public_decrypt($data, $result, $key, $padding);

		if($res === true){
			return $result;
		}else{
			msg::set('boa.error.144');
		}
	}

	public function private_enc($data, $padding = OPENSSL_PKCS1_PADDING){
		$key = $this->get_private_key();
		$res = openssl_private_encrypt($data, $result, $key, $padding);

		if($res === true){
			return $result;
		}else{
			msg::set('boa.error.145');
		}
	}

	public function private_dec($data, $padding = OPENSSL_PKCS1_PADDING){
		$key = $this->get_private_key();
		$res = openssl_private_decrypt($data, $result, $key, $padding);

		if($res === true){
			return $result;
		}else{
			msg::set('boa.error.146');
		}
	}

	public function sign($data, $sign_alg = null){
		if($sign_alg === null){
			$sign_alg = $this->cfg['sign_alg'];
		}

		if(in_array($sign_alg, openssl_get_md_methods())){
			$key = $this->get_private_key();
			$res = openssl_sign($data, $result, $key, $sign_alg);
		}else{
			msg::set('boa.error.142', $sign_alg);
		}

		if($res === true){
			return $result;
		}else{
			msg::set('boa.error.147');
		}
	}

	public function verify($data, $sign, $sign_alg = null){
		if($sign_alg === null){
			$sign_alg = $this->cfg['sign_alg'];
		}

		if(in_array($sign_alg, openssl_get_md_methods())){
			$key = $this->get_public_key();
			$res = openssl_verify($data, $sign, $key, $sign_alg);
		}else{
			msg::set('boa.error.142', $sign_alg);
		}

		if($res == -1){
			msg::set('boa.error.148');
		}else{
			return $res;
		}
	}

	private function get_public_key(){
		if(file_exists($this->cfg['pubkey'])){
			$pem = file_get_contents($this->cfg['pubkey']);
			return openssl_pkey_get_public($pem);
		}else{
			msg::set('boa.error.2', $this->cfg['pubkey']);
		}
	}

	private function get_private_key(){
		if(file_exists($this->cfg['prikey'])){
			$pem = file_get_contents($this->cfg['prikey']);
			return openssl_pkey_get_private($pem, $this->cfg['pripass']);
		}else{
			msg::set('boa.error.2', $this->cfg['prikey']);
		}
	}
}
?>