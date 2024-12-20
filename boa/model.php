<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.model.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class model extends base{
	protected $cfg = [
		'table'  => '',
		'prikey' => 'id',
		'pagesize' => 10,
	];

	public function get($id){
		return boa::db()->table($this->cfg['table'])->where($this->cfg['prikey'] .' = ?', $id)->find();
	}

	public function list($pagesize = 0){
		$page = boa::env('var.page');
		if($pagesize <= 0){
			$pagesize = $this->cfg['pagesize'];
		}
		$offset = ($page - 1) * $pagesize;
		return boa::db()->table($this->cfg['table'])->limit($offset, $pagesize)->order($this->cfg['prikey'] .' desc')->select();
	}

	public function add($data){
		return boa::db()->table($this->cfg['table'])->insert($data);
	}

	public function edit($id, $data){
		return boa::db()->table($this->cfg['table'])->where($this->cfg['prikey'] .' = ?', $id)->update($data);
	}

	public function del($id){
		return boa::db()->table($this->cfg['table'])->where($this->cfg['prikey'] .' = ?', $id)->delete();
	}
}
?>
