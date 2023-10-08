<?php
namespace boa;
?>

<!DOCTYPE HTML>
<html dir="<?php echo boa::lang('boa.locale.direction'); ?>">
<head>
	<title><?php echo boa::lang('boa.system.info'); ?> - <?php echo NAME; ?></title>
	<meta charset="<?php echo CHARSET; ?>">
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge, chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, minimum-scale=1.0">
	<link href="<?php echo WWW_RES; ?>msg.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<dl>
		<dt><?php echo boa::lang('boa.system.info'); ?></dt>
		<dd>
		<?php 
		foreach($msg as $k => $v){
			echo '<div class="msg">'. $v['msg'] .'</div>';
		}
		?>
		</dd>
	</dl>
</body>
</html>