<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.database.stmt.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\database;

use boa\boa;
use boa\msg;

class stmt{
	private $db;
	private $stmt;
	private $sql;
	private $para = [];

	public function __construct($sql, $db){
		$this->db = $db;
		$this->sql = $sql;
		$this->stmt = $this->db->prepare($sql);
	}

	public function execute($para = [], $type = ''){
		if($para){
			$this->para = $para;
			$this->db->stmt_bind($this->stmt, $para, $type);
		}
		$res = $this->stmt->execute();

		$sql = $this->sql();
		boa::log()->set('info', "[stmt]$sql");
		return $res;
	}

	public function one(){
		return $this->db->stmt_one($this->stmt);
	}

	public function all(){
		return $this->db->stmt_all($this->stmt);
	}

	public function lastid(){
		return $this->db->stmt_lastid($this->stmt);
	}

	public function affected(){
		return $this->db->stmt_affected($this->stmt);
	}

	private function sql(){
		$sql = $this->sql;
		foreach($this->para as $v){
			$v = is_null($v) ? 'NULL' : "'". addslashes($v) ."'";
			$sql = preg_replace('/\?/', $v, $sql, 1);
		}
		return $sql;
	}
}
?>