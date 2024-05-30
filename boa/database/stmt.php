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

	public function __construct($sql, $db){
		$this->db = $db;
		$this->sql = $sql;
		$this->stmt = $this->db->prepare($sql);
	}

	public function execute($para, $type = ''){
		$this->db->stmt_bind($para, $type);
		return $this->stmt->execute();
	}

	public function one(){
		return $this->db->stmt_one();
	}

	public function all(){
		return $this->db->stmt_all();
	}

	public function lastid(){
		return $this->db->stmt_lastid();
	}

	public function affected(){
		return $this->db->stmt_affected();
	}
}
?>