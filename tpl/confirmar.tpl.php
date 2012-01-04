<?php
if (empty($_GET['email']) || empty($_GET['hash']))
{
    echo '<p>Parece que se ha equivocado.</p>';
    return;
}

db::consultar('UPDATE `cuentas` SET `verificado` = 1 WHERE `correo` = "'.db::codex($_GET['email']).'" AND `hash` = "'.db::codex($_GET['hash']).'"');
if (db::afectadas())
{
    echo '<p>Gracias por confirmar su cuenta.</p>';
    // Iniciemole la sesión
    sesion::iniciar('correo',$_GET['email']);
} else {
    echo '<p>Parace que se ha equivocado.</p>';
}
?>