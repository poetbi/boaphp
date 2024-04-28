<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.database.driver.pdo.html
License : Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\database\driver;

use boa\boa;
use boa\msg;

class pdo{
	public $cfg = [
		'type' => 'mysql',
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
	private $type = [
		'i' => \PDO::PARAM_INT,
		'd' => \PDO::PARAM_STR,
		's' => \PDO::PARAM_STR,
		'b' => \PDO::PARAM_LOB,
		'o' => \PDO::PARAM_BOOL
	];
	private $mode = \PDO::FETCH_ASSOC;
	private $sql;

	public function __construct($cfg){
		if($cfg){
			$this->cfg = array_merge($this->cfg, $cfg);
		}

		$this->cfg['option'][\PDO::ATTR_DEFAULT_FETCH_MODE] = $this->mode;
		$this->cfg['option'][\PDO::ATTR_PERSISTENT] = $this->cfg['persist'];

		$dsn = $this->dsn($this->cfg['type']);
		try{
			$this->link = new \pdo($dsn, $this->cfg['user'], $this->cfg['pass'], $this->cfg['option']);
		}catch(\PDOException $e){
			msg::set('boa.error.101', 'pdo ('. $e->getCode() .')');
		}
	}

	public function execute($sql){
		return $this->link->exec($sql);
	}

	public function query($sql){
		$res = $this->link->query($sql);
		if($res){
			$res = $res->fetchAll();
		}
		$this->sql = $sql;
		return $res;
	}

	public function one($sql){
		$res = $this->link->query($sql);
		if($res){
			$res = $res->fetch();
			if(!$res){
				$res = [];
			}
		}
		return $res;
	}

	public function lastid($name = null){
		return $this->link->lastInsertId($name);
	}

	public function page($sql = null){
		if(!$sql){
			$sql = $this->sql;
			$sql = preg_replace('/select (.+?) from /i', 'SELECT COUNT(*) FROM ', $sql);
			$sql = preg_replace('/ limit [\d]+(\s*,\s*[\d]+)?/i', '', $sql);
			$sql = preg_replace('/ order by (.+) (asc|desc)/i', '', $sql);
		}
		$res = $this->link->query($sql);
		if($res){
			$rs = $res->fetch();
			$num = intval(current($rs));
		}else{
			$num = 0;
		}
		return $num;
	}

	public function begin(){
		return $this->link->beginTransaction();
	}

	public function commit(){
		return $this->link->commit();
	}

	public function rollback(){
		return $this->link->rollBack();
	}

	public function prepare($sql){
		return $this->link->prepare($sql);
	}

	public function stmt_bind($stmt, $para, $type = ''){
		$i = 0;
		foreach($para as $k => $v){
			$key = $k === $i ? $k+1 : ":$k";
			if(is_array($v)){
				if(count($v) > 2){
					$stmt->bindParam($key, $para[$k][0], $v[1], $v[2]);
				}else{
					$stmt->bindParam($key, $para[$k][0], $v[1]);
				}
			}else{
				if($type){
					$t = substr($type, $i, 1);
					$t = $this->type[$t];
					$stmt->bindParam($key, $para[$k], $t);
				}else{
					$stmt->bindParam($key, $para[$k]);
				}
			}
			$i++;
		}
	}

	public function stmt_one($stmt){
		return $stmt->fetch();
	}

	public function stmt_all($stmt){
		return $stmt->fetchAll();
	}

	public function stmt_lastid($stmt){
		return $this->link->lastInsertId();
	}

	public function stmt_affected($stmt){
		return $stmt->rowCount();
	}

	private function dsn($type){
		$tcp = [
			'mysql' => 'host={host};port={port};dbname={name}',
			'sqlsrv' => 'Server={host},{port};Database={name}',
			'oci' => 'dbname=//{host}:{port}/{name}',
			'pgsql' => 'host={host};port={port};dbname={name}',
			'ibm' => 'DRIVER={IBM DB2 ODBC DRIVER};DATABASE={name};HOSTNAME={host};PORT={port};PROTOCOL=TCPIP',
			'sqlite' => '{name}',
			'sqlite2' => '{name}',
			'odbc' => 'Driver={Microsoft Access Driver (*.mdb)};Dbq={name}',
			'firebird' => 'dbname={host}/{port}:{name}',
			'cubrid' => 'host={host};port={port};dbname={name}',
			'mssql' => 'mssql:host={host};dbname={name}',
			'sybase' => 'sybase:host={host};dbname={name}',
			'dblib' => 'dblib:host={host};dbname={name}',
			'informix' => 'informix:DSN={name}'
		];
		$socket = [
			'mysql' => 'unix_socket={host};dbname={name}',
			'sqlsrv' => 'Server={host};Database={name}',
			'oci' => 'dbname={name}',
			'ibm' => 'DSN={name}',
			'sqlite' => ':memory:',
			'sqlite2' => ':memory:',
			'odbc' => '{name}',
			'firebird' => 'dbname={name}'
		];
		if($this->cfg['port'] == 0 && array_key_exists($type, $socket)){
			$dsn = $socket[$type];
		}else{
			$dsn = $tcp[$type];
		}
		if($dsn){
			$arr = ['host', 'port', 'name'];
			foreach($arr as $key){
				$dsn = str_replace('{'. $key .'}', $this->cfg[$key], $dsn);
			}
			$dsn = $type .':'. $dsn;
			if($this->cfg['charset'] && in_array($type, ['mysql', 'oci', 'firebird', 'mssql', 'sybase', 'dblib'])){
				$dsn .= ';charset='. $this->cfg['charset'];
			}
		}
		return $dsn;
	}
}
?>
