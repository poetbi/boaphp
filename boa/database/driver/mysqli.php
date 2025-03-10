<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.database.driver.mysqli.html
License : Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\database\driver;

use boa\boa;
use boa\msg;

class mysqli extends \boa\database\base{
	public $cfg = [
		'charset' => 'utf8',
		'persist' => false,
		'option' => [],
		'host' => 'localhost',
		'port' => 3306,
		'name' => '',
		'user' => null,
		'pass' => null,
	];
	private $link;
	private $mode = \MYSQLI_ASSOC;
	private $sql;
	private $stmt;

	public function __construct($cfg){
        if($cfg){
			$this->cfg = array_merge($this->cfg, $cfg);
		}

		if($this->cfg['persist']){
			$this->cfg['host'] = 'p:'. $this->cfg['host'];
		}

		if($this->cfg['port'] == 0){
			$this->link = new \mysqli($this->cfg['host'], $this->cfg['user'], $this->cfg['pass'], $this->cfg['name']);
		}else{
			$this->link = new \mysqli($this->cfg['host'], $this->cfg['user'], $this->cfg['pass'], $this->cfg['name'], $this->cfg['port']);
		}

		if($this->link->connect_errno){
			msg::set('boa.error.101', 'mysqli ('. $this->link->connect_errno .')');
		}

		$this->link->set_charset($this->cfg['charset']);

		if($this->cfg['option']){
			foreach($this->cfg['option'] as $k => $v){
				$this->link->options($k, $v);
			}
		}
	}

	public function execute($sql){
		$res = $this->link->real_query($sql);
		if($res === false){
			return false;
		}else{
			return $this->link->affected_rows;
		}
	}

	public function query($sql){
		$res = $this->link->query($sql);
		if($res){
			$res = $res->fetch_all($this->mode);
		}
		$this->sql = $sql;
		return $res;
	}

	public function one($sql){
		$res = $this->link->query($sql);
		if($res){
			$res = $res->fetch_assoc();
			if(!$res){
				$res = [];
			}
		}
		return $res;
	}

	public function lastid($name = null){
		return $this->link->insert_id;
	}

	public function page($sql = null){
		if(!$sql) $sql = $this->pagesql($this->sql);
		$res = $this->link->query($sql);
		if($res){
			$rs = $res->fetch_row();
			$num = intval(current($rs));
		}else{
			$num = 0;
		}
		return $num;
	}

	public function begin(){
		return $this->link->begin_transaction();
	}

	public function commit(){
		return $this->link->commit();
	}

	public function rollback(){
		return $this->link->rollback();
	}
	
	public function prepare($sql){
		$this->stmt = $this->link->prepare($sql);
		return $this->stmt;
	}

	public function stmt_bind($para, $type = ''){
		if(!$type) $type = str_repeat('s', count($para));
		array_unshift($para, $type);
		call_user_func_array(array($this->stmt, 'bind_param'), $para);
	}

	public function stmt_one(){
		$res = $this->stmt->get_result();
		return $res->fetch_assoc();
	}
	
	public function stmt_all(){
		$res = $this->stmt->get_result();
		return $res->fetch_all($this->mode);
	}
	
	public function stmt_lastid(){
		return $this->stmt->insert_id;
	}
	
	public function stmt_affected(){
		return $this->stmt->affected_rows;
	}
}
?>
