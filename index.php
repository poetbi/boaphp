<?php
define('BS_ROOT', str_replace('\\', '/', dirname(__FILE__)) .'/');
define('BS_WWW', BS_ROOT .'www/');
require(BS_ROOT .'boa/boa.php');
\boa\boa::start();
?>