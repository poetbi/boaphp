<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.database.base.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\database;

class base{
	protected function escape($v){
		$arr = ['mysql', 'pgsql'];
		if(in_array($this->cfg['type'], $arr)){
			$v = addslashes($v);
		}else{
			$v = $this->_escape($v);
		}

		return "'$v'";
	}

	protected function _escape($v){
		return str_replace("'", "''", $v);
	}

	protected function pagesql($sql){
		$sql = preg_replace('/select (.+?) from /i', 'SELECT COUNT(*) FROM ', $sql);
		$sql = preg_replace('/ limit [\d]+(\s*,\s*[\d]+)?/i', '', $sql);
		$sql = preg_replace('/ order by (.+) (asc|desc)/i', '', $sql);
		return $sql;
	}
}
?>