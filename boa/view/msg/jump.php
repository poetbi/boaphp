<?php
namespace boa;
?>

<!DOCTYPE HTML>
<html dir="<?php echo boa::lang('boa.locale.direction'); ?>">
<head>
	<title><?php echo boa::lang('boa.system.jump'); ?> - <?php echo NAME; ?></title>
	<meta charset="<?php echo CHARSET; ?>">
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge, chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, minimum-scale=1.0">
	<link href="<?php echo WWW_RES; ?>msg.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<dl class="lost">
		<dt><h3>
			<?php
				if($tip) echo "$tip, ";
				echo boa::lang('boa.system.jump');
			?>
			(<span id="time"><?php echo $sec; ?></span>)
		</h3></dt>
	</dl>
<script type="text/javascript">
	var time = document.getElementById("time");
	var sec = <?php echo $sec; ?>;
	var timer = setInterval(function(){
		sec--;
		if(sec > 0){
			time.innerHTML = sec;
		}else{
			clearInterval(timer);
			location.href = "<?php echo $url; ?>";
		}
	}, 1000);
</script>
</body>
</html>
