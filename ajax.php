<?php
error_reporting(E_STRICT | E_ALL);
require_once('config.php');
require_once('db.php');
require_once('stubs.php');
require_once('ui.php');
require_once('sesion.php');

if (sesion::iniciado() && isset($_POST['eliminar']) && is_numeric($_POST['eliminar']))
{
    db::consultar('DELETE FROM fotos WHERE ID_foto="'.db::codex($_POST['eliminar']).'" AND ID_cuenta="'.sesion::info('ID_cuenta').'" LIMIT 1');
    return;
}

if (sesion::iniciado() && isset($_GET['misfotos']))
{
    $c = 'SELECT ID_foto, hash FROM fotos WHERE ID_cuenta='.sesion::info('ID_cuenta');
    $r = db::consultar($c);
    
    while ($f = mysql_fetch_assoc($r))
    {
        echo '<div class="misfotos"><img src="crop_100_100_'.$f['hash'].'.jpg" /><input class="eliminar" type="button" rel="'.$f['ID_foto'].'" value="X" /></div>';
    }
    return;
}

if (sesion::iniciado() && isset($_GET['misfotos2']))
{
    $_GET['mias'] = true;
    $_GET['sinaprobar'] = true;
    stubs::CrearRejilla(array('nopaginacion' => true));
    return;
}


if (isset($_GET['quetanbuena']) && isset($_GET['if']) && isset($_GET['ic']))
{
    if (isset($_POST['nota']) && is_numeric($_POST['nota']) && $_POST['nota'] > 0 && $_POST['nota'] < 6 ) {
        $datos['ID_foto'] = $_GET['if'];
        $datos['rating'] = $_POST['nota'];
        db::insertar('votos',$datos);
        $_GET['voto'] = '1';
    }
    
    $c = 'SELECT cuentas.ID_cuenta, tf.ID_foto, tf.hash AS "foto_hash", (SELECT COUNT(*) FROM fotos AS tf2 WHERE tf2.ID_cuenta = tf.ID_cuenta) AS cantidad_fotos, `pais`, COALESCE(cantidad_votos_a,0) AS cantidad_votos, FORMAT(COALESCE(rating_promedio_a,0),1) AS "rating_promedio", cantidad_vistas, tf.creacion, usuario FROM fotos as tf LEFT JOIN cuentas USING(ID_cuenta) LEFT JOIN (SELECT ID_foto, COUNT(*) AS "cantidad_votos_a", AVG(rating) AS "rating_promedio_a" FROM votos GROUP BY ID_foto) AS tv USING(ID_foto) LEFT JOIN datos_pais USING(ID_pais) WHERE ID_foto="'.db::codex($_GET['if']).'" GROUP BY ID_foto';
    $r = db::consultar($c);
    if ($r)
        $f = mysql_fetch_assoc($r);
    else
        return;
    
    echo '<p>';
    if (isset($_GET['voto']) && $_GET['voto'] == '1') {
        echo 'Gracias por su voto.';
    } else {
        echo '¿Que tan buena esta? <a href="#" title="para nada buena" rel="1" class="nota">1</a> <a href="#" title="peor es nada" rel="2" class="nota">2</a> <a href="#" title="regular" rel="3" class="nota">3</a> <a href="#" title="esta buena" rel="4" class="nota">4</a> <a href="#" title="si esta bien buena!" rel="5" class="nota">5</a>';
    }
    echo ' <a href="'.PROY_URL.'conocer_chica_'.$f['ID_cuenta'].'_'.$f['ID_foto'].'_'.$f['usuario'].'.html" id="contactar" class="conectar">Contactar</a> <a href="'.PROY_URL.'reportar.html?if='.$f['ID_foto'].'"  class="reportar">Reportar</a>';
    echo '</p>';
    echo '<div id="ver_datos"><span class="rating">Rating <span class="valorRating">'.$f['rating_promedio'].'/5.0</span> <span class="votos">'.$f['cantidad_votos'].' votos</span> <span class="tiempoPublicacion"></span> <span class="vistas">'.$f['cantidad_vistas'].' vistas</span></div>';
    
    echo "<script>$('#contactar').facebox();</script>";

    return;
}

if (sesion::iniciado() && !empty($_POST['ID_destino']) && !empty($_POST['mensaje']))
{
    $datos = array();
    $datos['ID_origen'] = sesion::info('ID_cuenta');
    $datos['ID_destino'] = $_POST['ID_destino'];
    $datos['canal'] = sha1(min(sesion::info('ID_cuenta'),$_POST['ID_destino']).'+'.max(sesion::info('ID_cuenta'),$_POST['ID_destino']));
    $datos['mensaje'] = strip_tags($_POST['mensaje']);
    $datos['fecha_enviado'] = date('Y-m-d H:i:s');
    db::insertar('mensajes',$datos);
    return;
}

if (sesion::iniciado() && !empty($_GET['ajax']) && $_GET['ajax'] == 'ver_mensajes' && !empty($_GET['canal']))
{
    $c = 'SELECT `ID_mensaje`, `ID_origen`, `ID_destino`, `mensaje`, `estado`, `fecha_enviado`, `canal` FROM (SELECT * FROM `mensajes` WHERE `canal` = "'. $_GET['canal'].'" ORDER BY `fecha_enviado` DESC LIMIT 25) AS a WHERE 1 ORDER BY `fecha_enviado` ASC';
    $r = db::consultar($c);
    $cache = '';
    $buffer = '';
    $ultimaClass = '';
    
    while ($f = mysql_fetch_assoc($r))
    {
        $class = ($f['ID_origen'] == sesion::info('ID_cuenta')) ? 'remoto' : 'origen';
        
        if (sesion::info('tipo') == 'perra')
            $img = ($f['ID_origen'] == sesion::info('ID_cuenta')) ? 'remoto2' : 'origen2';
        else
            $img = ($f['ID_origen'] == sesion::info('ID_cuenta')) ? 'origen' : 'remoto';
        
        if ($ultimaClass != '' && $class != $ultimaClass)
        {
            $cache .= '<div class="mensaje '.$ultimaClass.'">'.$buffer.'</div>'."\n";
            $buffer = '';
        }
        $buffer .= '<span class="elemento"><img title="'.$f['fecha_enviado'].'" src="img/icono_msj_' . $img . '.gif" /> '.$f['mensaje'].'</span><br />';
        $ultimaClass = $class;
    }

    if ($buffer != '')
    {
        $cache .= '<div class="mensaje '.$ultimaClass.'">'.$buffer.'</div>'."\n";
        $buffer = '';
    }
    
    echo $cache;
    
    $c = 'UPDATE `mensajes` SET `estado` = "leido" WHERE `ID_destino` = "'.sesion::info('ID_cuenta').'"';
    db::consultar($c);
    return;
}

echo '<pre>¿Que?</pre>';
?>