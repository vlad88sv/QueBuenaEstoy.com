<?php
$c = 'SELECT tf.ID_cuenta, tf.ID_foto, tf.hash AS "foto_hash", (SELECT COUNT(*) FROM fotos AS tf2 WHERE tf2.ID_cuenta = tf.ID_cuenta) AS cantidad_fotos, `pais`, tf.creacion, usuario FROM fotos as tf LEFT JOIN cuentas USING(ID_cuenta) LEFT JOIN datos_pais USING(ID_pais) WHERE tf.ID_foto="'.db::codex($_GET['ID_foto']).'" AND tf.ID_cuenta="'.db::codex($_GET['ID_cuenta']).'"';
$r = db::consultar($c);

if (!$r || !$f = mysql_fetch_assoc($r))
    return;

$HEAD_title = 'viendo a ' . $f['usuario']. ' de '. $f['pais'];

if ((sesion::info('tipo') != 'perra') && !empty($_POST['comentario']) && strlen($_POST['comentario']) > 5)
{
    unset($datos);
    $datos['comentario'] = strip_tags($_POST['comentario']);
    $datos['ID_foto'] = $f['ID_foto'];
    $datos['ID_cuenta'] = sesion::info('ID_cuenta');
    db::insertar('comentario',$datos,false);
    unset($datos);
}    
    
if(empty($_COOKIE['vista']))
    db::consultar('UPDATE fotos set cantidad_vistas=cantidad_vistas+1 WHERE ID_foto='.$f['ID_foto']);

$c = 'SELECT fotos.ID_cuenta, ID_foto, fotos.hash, usuario FROM fotos LEFT JOIN cuentas USING(ID_cuenta) WHERE ID_foto <> '.$f['ID_foto'].' AND ID_cuenta="'.$_GET['ID_cuenta'].'" ORDER BY ID_foto ASC';
$r = db::consultar($c);

$mas_fotos = null;
while ($ff = mysql_fetch_assoc($r))
    $mas_fotos .= '<li><a style="display:inline-block;margin:0 3px;" href="'.PROY_URL.'chicas_lindas_'.$ff['ID_cuenta'].'_'.$ff['ID_foto'].'_'.$ff['usuario'].'.html"><img src="crop_150_150_'.$ff['hash'].'" /></a></li>';

// Obtener foto anterior:

$foto_anterior = null;
$foto_siguiente = null;

$c = 'SELECT tf.ID_cuenta, tf.ID_foto,  usuario FROM fotos as tf LEFT JOIN cuentas USING(ID_cuenta) WHERE tf.ID_cuenta="'.db::codex($_GET['ID_cuenta']).'" AND tf.ID_foto<"'.db::codex($_GET['ID_foto']).'" ORDER BY tf.ID_foto DESC LIMIT 1';
if ($rr = db::consultar($c))
{
    if ($ff = mysql_fetch_assoc($rr))
    {
        $foto_anterior = '<a id="izq" href="chicas_lindas_'.$ff['ID_cuenta'].'_'.$ff['ID_foto'].'_'.$ff['usuario'].'.html"><img src="img/icono_flecha_izq.png" /></a>';
    } else {
        $c = 'SELECT tf.ID_cuenta, tf.ID_foto,  usuario FROM fotos as tf LEFT JOIN cuentas USING(ID_cuenta) WHERE tf.ID_cuenta="'.db::codex($_GET['ID_cuenta']).'" ORDER BY tf.ID_foto DESC LIMIT 1';
        if ($rr = db::consultar($c))
            if ($ff = mysql_fetch_assoc($rr))
                $foto_anterior = '<a id="izq" href="chicas_lindas_'.$ff['ID_cuenta'].'_'.$ff['ID_foto'].'_'.$ff['usuario'].'.html"><img src="img/icono_flecha_izq.png" /></a>';
    }
    
}
$c = 'SELECT tf.ID_cuenta, tf.ID_foto,  usuario FROM fotos as tf LEFT JOIN cuentas USING(ID_cuenta) WHERE tf.ID_cuenta="'.db::codex($_GET['ID_cuenta']).'" AND tf.ID_foto>"'.db::codex($_GET['ID_foto']).'" LIMIT 1';
if ($rr = db::consultar($c))
{
    if ($ff = mysql_fetch_assoc($rr))
    {
        $foto_siguiente = '<a id="der" href="chicas_lindas_'.$ff['ID_cuenta'].'_'.$ff['ID_foto'].'_'.$ff['usuario'].'.html"><img src="img/icono_flecha_der.png" /></a>';
    } else {
        $c = 'SELECT tf.ID_cuenta, tf.ID_foto,  usuario FROM fotos as tf LEFT JOIN cuentas USING(ID_cuenta) WHERE tf.ID_cuenta="'.db::codex($_GET['ID_cuenta']).'" ORDER BY tf.ID_foto ASC LIMIT 1';
        if ($rr = db::consultar($c))
            if ($ff = mysql_fetch_assoc($rr))
                $foto_siguiente = '<a id="der" href="chicas_lindas_'.$ff['ID_cuenta'].'_'.$ff['ID_foto'].'_'.$ff['usuario'].'.html"><img src="img/icono_flecha_der.png" /></a>';
    }
}   

?>
<div id="tituloVista">
    <?php echo $f['usuario']. ' de '. $f['pais']; ?>
</div>

<div id="ver_chica">
<table style="width: 100%;">
    <tr>
        <td style="vertical-align: top;"><?php echo $foto_anterior; ?></td>
        <td style="width: 800px;"><img src="<?php echo PROY_URL.'imagen_800_0_'.$f['foto_hash']; ?>.jpg" /></td>
        <td style="vertical-align: top;"><?php echo $foto_siguiente; ?></td>
    </tr>
</table>

<table style="width: 720px;margin:auto;">
<tr>
<td style="width: 490px;"><div id="quetanbuena"></div></td>
<td style="width: 220px; text-align: right;">
    <a href="<?php echo PROY_URL.'conocer_chica_'.$f['ID_cuenta'].'_'.$f['ID_foto'].'_'.$f['usuario'].'.html'; ?>" id="contactar" class="conectar">Contactar</a> <a href="<?php echo PROY_URL.'reportar.html?if='.$f['ID_foto']; ?>"  class="reportar">Reportar</a>
</td>
</tr>
</table>
</div>

<?php if (!empty($mas_fotos)): ?>
<div id="mas_fotos"><p style="padding-left: 25px;">Mas fotos de <?php echo $f['usuario']; ?></p>
<table style="width:100%;border-collapse:collapse;table-layout: fixed;">
    <tr>
    <td><a href="#" id="mycarousel-prev">&lt;&lt;</a></td><td style="width: 900px;"><div id="mas_fotos_galeria" class="jcarousel-skin-tango"><ul><?php echo $mas_fotos ; ?></ul></div></td><td><a href="#" id="mycarousel-next">&gt;&gt;</a></td>
    </tr>
</table>
</div>
<?php endif; ?>

<div id="comentarios">
<?php
if (!sesion::iniciado())
{
    echo '<p>Registrate o inicia sesión y enterate que estan comentando los demas sobre ella!, es gratis!. <a class="conectar" href="'.PROY_URL.'iniciar.html">Registrarse / Iniciar sesión</a></p>';
} else {
    echo '
    <br />
    <div class="error_burbuja" style="text-align:center;">
    <p>Ellas ven tus comentarios pero no pueden responder aquí, si quieres conocerla utiliza el botón "Contactar".</p>
    <p>Ellas serán notificadas de tu contacto y te contestarán segun tus deseos en el menor tiempo posible.</p>
    </div>
    ';
    
    $c = 'SELECT `ID_comentario`, `ID_cuenta`, `ID_foto`, `comentario`, `hora`, `usuario` FROM `comentario` LEFT JOIN `cuentas` USING (ID_cuenta) WHERE ID_foto='.$f['ID_foto'];
    $rr = db::consultar($c);
    
    if (sesion::info('tipo') != 'perra') echo '<div id="comentarios">
    <form method="post" id="comentar" action="'.PROY_URL_ACTUAL.'">
        <p>Enviale tu comentario</p>
        Comentario:<br />
        <textarea name="comentario"></textarea><br />
        <div style="text-align:right;padding-right:20px;"><input type="submit" value="Enviar" /></div>
    </form>';

    if ($rr)
    {
    
        echo '<p>Comentarios ('.mysql_num_rows($rr).')</p>';
    
        while ($ff = mysql_fetch_assoc($rr))
        {

            echo '<div class="unidadComentario">';
            echo '<hr />';
            echo '<div><img src="img/icono_comentario.png" /> <span class="usuario">'.$ff['usuario'].'</span> <span class="hora">'.stubs::timesince($ff['hora']).'</span></div>';
            echo '<p class="comentario">'.$ff['comentario'].'</p>';
            echo '</div>';
        }
    }
}
?>
</div>

<script>
    function mycarousel_initCallback(carousel) {
    
        $('#mycarousel-next').bind('click', function() {
            carousel.next();
            return false;
        });
    
        $('#mycarousel-prev').bind('click', function() {
            carousel.prev();
            return false;
        });
    };

    $(function(){
        $.cookie('vista', '1', { expires: 7, path:  window.location.pathname });
        
        function CargarRating()
        {
            $("#quetanbuena").load('<?php echo PROY_URL; ?>ajax?quetanbuena&voto=' + ($.cookie('voto') == "1" ? "1" : "0") +'&if=<?php echo $f['ID_foto']; ?>&ic=<?php echo $f['ID_cuenta']; ?>');
        }
        
        CargarRating();
        
        $(".nota").live('click',function(event){
            event.preventDefault();
            $.post('<?php echo PROY_URL; ?>ajax?quetanbuena&if=<?php echo $f['ID_foto']; ?>&ic=<?php echo $f['ID_cuenta']; ?>',{nota:$(this).attr('rel')},function(data) {$('#quetanbuena').html(data);});
            $.cookie('voto', '1', { expires: 30, path:  window.location.pathname});
        });
        
        jQuery("#mas_fotos_galeria").jcarousel({
            scroll: 1,
            initCallback: mycarousel_initCallback,
            // This tells jCarousel NOT to autobuild prev/next buttons
            buttonNextHTML: null,
            buttonPrevHTML: null
         });
        
        $(document).bind('keydown', function(e) {
            if(e.which==37) window.location.href = $('#izq').attr('href');
            
            if(e.which==39) window.location.href = $('#der').attr('href');
        });
    });
</script>