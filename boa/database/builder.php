<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.database.builder.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\database;

use boa\boa;
use boa\msg;

class builder extends \boa\database\base{
	public $cfg = [
		'type' => 'mysql'
	];

	private $select = 'SELECT %distinct%%field% FROM %table%%force%%join%%where%%group%%having%%order%%limit%%union%%lock%';
	private $insert = 'INSERT INTO %table%%fields% VALUES %values%';
	private $update = 'UPDATE %table% SET %fields%%where%';
	private $delete = 'DELETE FROM %table%%join%%where%%order%%limit%';
	private $data = [];
	private $getsql = false;

	public function __construct($table, $type){
		$this->data['table'] = $table;
		$this->cfg['type'] = $type;
	}

	public function distinct($field){
		if($field) $this->data['distinct'] = "DISTINCT($field), ";
	}

	public function field($field){
		if($field) $this->data['field'] = $field;
	}

	public function force($index){
		$this->data['force'] = " FORCE INDEX($index)";
	}

	public function join($table, $on, $type = 'LEFT'){
		if(!array_key_exists('join', $this->data)) $this->data['join'] = '';
		$this->data['join'] .= " $type JOIN $table ON $on";
	}

	public function where($where){
		if($where) $this->data['where'] = " WHERE $where";
	}

	public function group($field){
		if($field) $this->data['group'] = " GROUP BY $field";
	}

	public function having($where){
		if($where) $this->data['having'] = " HAVING $where";
	}

	public function order($order){
		if($order) $this->data['order'] = " ORDER BY $order";
	}

	public function limit($limit){
		if($limit) $this->data['limit'] = " LIMIT $limit";
	}

	public function union($sql){
		if($sql) $this->data['union'] .= " UNION $sql";
	}

	public function union_all($sql){
		if($sql) $this->data['union'] .= " UNION ALL $sql";
	}

	public function lock($lock){
		switch($lock){
			case 'share':
				$sql = ' LOCK IN SHARE MODE';
				break;

			case 'update':
				$sql = ' FOR UPDATE';
				break;
		}
		if($sql) $this->data['lock'] = $sql;
	}

	public function getsql(){
		$this->getsql = true;
	}

	public function select($type, $db){
		if(!array_key_exists('field', $this->data)){
			if(array_key_exists('distinct', $this->data)){
				msg::set('boa.error.104');
			}else{
				$this->data['field'] = '*';
			}
		}

		$sql = $this->select;
		preg_match_all('/%(\w+)%/', $sql, $arr);
		foreach($arr[1] as $k => $v){
			$str = array_key_exists($v, $this->data) ? $this->data[$v] : '';
			$sql = str_replace($arr[0][$k], $str, $sql);
		}

		if($this->getsql){
			return $sql;
		}else{
			$method = $type == 1 ? 'one' : 'query';
			$res = $db->$method($sql);
			boa::log()->set('info', "[builder]$sql");
			return $res;
		}
	}

	public function insert($data, $db){
		$this->data['fields'] = ' ('. implode(', ', array_keys($data)) .')';
		$values = array_values($data);
		foreach($values as $k => $v){
			if($v === null){
				$values[$k] = 'NULL';
			}else{
				$values[$k] = $this->escape($v);
			}
		}
		$this->data['values'] = '('. implode(', ', $values) .')';

		$res = $this->exec_sql($db, $this->insert);
		if(!$this->getsql && $res !== false){
			if($db->cfg['driver'] == 'pdo'){
				$res = $db->lastid();
			}
		}
		return $res;
	}

	public function update($data, $db){
		$fields = '';
		foreach($data as $k => $v){
			if($v === null){
				$fields .= ", $k = NULL";
			}else{
				$fields .= ", $k = ". $this->escape($v);
			}
		}
		$this->data['fields'] = substr($fields, 1);
		return $this->exec_sql($db, $this->update);
	}

	public function delete($db){
		return $this->exec_sql($db, $this->delete);
	}

	private function exec_sql($db, $sql){
		preg_match_all('/%(\w+)%/', $sql, $arr);
		foreach($arr[1] as $k => $v){
			$str = array_key_exists($v, $this->data) ? $this->data[$v] : '';
			$sql = str_replace($arr[0][$k], $str, $sql);
		}

		if($this->getsql){
			return $sql;
		}else{
			$res = $db->execute($sql);
			boa::log()->set('info', "[builder]$sql");
			return $res;
		}
	}
}
?>