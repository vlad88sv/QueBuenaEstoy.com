<?php
error_reporting(E_STRICT | E_ALL);
require_once('config.php');
require_once('db.php');
require_once('stubs.php');
require_once('ui.php');
require_once('sesion.php');
?>
<?php
$archivo = 'tpl/'.$_GET['accion'].'.tpl.php';

if (is_file($archivo))
    require_once($archivo); 
else
    echo '<p>ERROR "'.$_GET['accion'].'"</p>';
?>