<?php
error_reporting(E_STRICT | E_ALL);
require_once('config.php');
require_once('db.php');
require_once('stubs.php');

if (isset($_GET['crop']))
    stubs::crop_imagen($_GET['hash'],$_GET['ancho'].'_'.$_GET['alto'].'_'.$_GET['hash'],$_GET['ancho']);
else
    stubs::crear_imagen($_GET['hash'],$_GET['ancho'].'_'.$_GET['alto'].'_'.$_GET['hash'],$_GET['ancho'],$_GET['alto']);
?>