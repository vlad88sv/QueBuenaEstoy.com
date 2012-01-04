<?php
if (isset($_POST['if']) && !empty($_POST['razon']))
{
    db::insertar('reportes',array('ID_foto' => $_POST['if'], 'razon' => $_POST['razon']));
    echo '<p>Gracias por su reporte. <a href="'.PROY_URL.'">Continuar</a></p>';
    return;
}
?>
<h1>Reporte de fotografías/usuarios</h1>
<p>
Gracias por dedicar un tiempo para reportar imagenes indebidas.<br />
Las imagenes indebidas son:
<ul>
    <li>Fotos que representen o describan a menores de edad.</li>
    <li>Actividad sexual explicita</li>
    <li>Actividades que representen o promuevan violencia u odio</li>
    <li>Fotografías que hayan sido públicadas sin el consentimiento de la modelo</li>
</ul>
</p>
<form action="reportar.html" method="post">
    <input type="hidden" name="if" value="<?php echo $_GET['if']; ?>" />
    <label for="correo">Correo</label>
    <input type="text" name="correo" id="correo" value="<?php echo sesion::info('correo'); ?>" />
    <br />
    <label for="razon">Razón de reporte</label>
    <textarea style="width:100%;height:50px;" id="razon" name="razon"></textarea>
    <input type="submit" value="Enviar" />
</form>