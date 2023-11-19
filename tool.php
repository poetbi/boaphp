<?php
use \boa\boa;
use \boa\msg;

//error_reporting(0);
define('DEBUG', false);
if(!$_GET['type']) $_GET['type'] = 'validate';
define('BS_ROOT', str_replace('\\', '/', dirname(__FILE__)) .'/');
define('BS_WWW', BS_ROOT .'www/');
require(BS_ROOT .'boa/boa.php');
boa::init();

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	function rule(){
		$rule = [];
		$arr = explode(',', $_POST['rule']);
		foreach($arr as $v){
			$v = trim($v);
			if($v){
				$sub = explode('=>', $v);
				$key = trim($sub[0], " '\"");
				$val = trim($sub[1], " '\"");
				$rule[$key] = $val;
			}
		}
		return $rule;
	}

	switch($_GET['type']){
		case 'perm':
			$group = 'temp-' . time();
			$file = BS_WWW .'cfg/perm-'. $group .'.php';
			$str = '<?php return ['. trim($_POST['rule']) .']; ?>';
			file_put_contents($file, $str);
			boa::cache()->clear();
			
			$arr = explode('.', trim($_POST['act']));
			boa::env('mod', $arr[0]);
			boa::env('con', $arr[1]);
			boa::env('act', $arr[2]);

			$res = boa::permission()->check($group, $_POST['mode']);
			if($res){
				echo '通过';
			}else{
				echo '拒绝';
			}
			unlink($file);
		break;
		
		case 'validate':
			msg::set_type($_POST['msg']);
			$rule = rule();
			echo boa::validater()->execute('var', $_POST['var'], $rule);
		break;
	}
	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta name="renderer" content="webkit"/>
    <meta name="force-rendering" content="webkit"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">    
    <title>boaPHP规则测试工具</title>
	<style type="text/css">
	html, body{
		margin: 0;
		padding: 0;
		width: 100%;
		height: 100%;
		overflow: hidden;
	}
	body{
		display: flex;
		justify-content: center;
		align-items: center;
		flex-direction: column;
		font-size: 14px;
		font-weight: 200;
	}
	h1{
		font-weight: 200;
	}
	table{
		background: #CCC;
	}
	tr{
		background: #FFF;
	}
	a{
		text-decoration: none;
		color: #e6870c;
	}
	input[type='button']{
		padding: 8px 50px;
	}
	</style>
</head>
<body>
<?php
switch($_GET['type']){
	case 'perm':
?>
	<h1>权限验证规则</h1>
	<table cellpadding="10" cellspacing="1">
		<tr><td>动作：</td><td><input placeholder="待测试动作，如：home.index.test" name="act" size="75" required></td></tr>
		<tr><td>规则：</td><td><textarea placeholder="/* 注意：不包含首尾[] */

'deny' => '*',
'allow' => '...'" name="rule" cols="77" rows="10" required></textarea></td></tr>
		<tr><td>模式：</td><td><select name="mode">
			<option value="a">a</option>
			<option value="d">d</option>
			<option value="ad">ad</option>
			<option value="da">da</option>
		</select></td></tr>
		<tr><td>结果：</td><td><div id="result"></div></td></tr>
		<tr><td colspan="2" align="center"><input type="button" value=" 测试 " onclick="perm()"></td></tr>
	</table>
<?php
	break;
	
	case 'validate':
?>
	<h1>自动验证规则</h1>
	<table cellpadding="10" cellspacing="1">
		<tr><td>变量：</td><td><input placeholder="待测试变量的值" name="var" size="75"></td></tr>
		<tr><td>规则：</td><td><textarea placeholder="/* 注意：不包含首尾[] */

'check' => '...',
'filter' => '...'" name="rule" cols="77" rows="10" required></textarea></td></tr>
		<tr><td>类型：</td><td><select name="msg">
			<option value="msg">msg</option>
			<option value="json">json</option>
			<option value="xml">xml</option>
			<option value="jsonp">jsonp</option>
			<option value="str">str</option>
		</select></td></tr>
		<tr><td>结果：</td><td><textarea name="result" cols="77" rows="10"></textarea><div id="result"></div></td></tr>
		<tr><td colspan="2" align="center"><input type="button" value=" 测试 " onclick="validate()"></td></tr>
	</table>
<?php
	break;
}
?>
<script type="text/javascript">
	function _(name){
		return document.getElementsByName(name)[0];
	}
	
	function perm(){
		if(_('rule').value != ""){
			var data = "act="+ encodeURIComponent(_('act').value);
			data += "&rule="+ encodeURIComponent(_('rule').value);
			data += "&mode="+ encodeURIComponent(_('mode').value);
			post(data);
		}else{
			alert('规则必填');
		}
	}
	
	function validate(){
		if(_('rule').value != ""){
			var data = "var="+ encodeURIComponent(_('var').value);
			data += "&rule="+ encodeURIComponent(_('rule').value);
			data += "&msg="+ encodeURIComponent(_('msg').value);
			post(data);
		}else{
			alert('规则必填');
		}
	}
	
	function post(data){
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function() {
			if (xhr.readyState == 4 && xhr.status === 200) {
				var result = document.getElementById("result");
				if(_('result')){
					var msg = _('msg');
					if(msg && msg.value == 'msg'){
						_('result').style.display = "none";
						result.innerHTML = xhr.response;
					}else{
						_('result').style.display = "";
						result.innerHTML = "";
						_('result').value = xhr.response;
					}
				}else{
					result.innerHTML = xhr.response;
				}
			}
		}
		xhr.open('POST', "?type=<?php echo $_GET['type'] ?>");
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=UTF-8");
		xhr.send(data);
	}
</script>
	<p><a href="?type=perm">权限验证</a> &nbsp;  &nbsp; <a href="?type=validate">变量验证</a></p>
</body>
</html>