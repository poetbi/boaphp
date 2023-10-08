<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.console.input.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa\console;

use boa\boa;

class input{
    public function prompt($tip = '', $rule = [], $key = 'input'){
		fwrite(STDOUT, "$tip: ");
		if(function_exists('readline')){
			$val = readline();
		}else{
			$val = fgets(STDIN);
		}

		if($rule){
			$val = boa::validater()->execute($key, $val, $rule);
		}
		return $val;
    }
}
?>