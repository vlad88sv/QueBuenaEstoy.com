<?php
error_reporting(E_STRICT | E_ALL);
require_once('config.php');
require_once('db.php');
require_once('stubs.php');
echo stubs::correo('vladimiroski@gmail.com','Prueba '.microtime(),'Esta es una prueba desde QueBuenaEstoy.com y prueba_correo.php');
?>


