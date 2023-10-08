<?php
define('BS_WWW', str_replace('\\', '/', dirname(__FILE__)) .'/');
define('BS_ROOT', preg_replace('/[^\/]+\/$/', '', BS_WWW));
require(BS_ROOT .'boa/boa.php');
\boa\boa::start();
?>