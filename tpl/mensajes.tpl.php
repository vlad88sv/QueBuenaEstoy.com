<?php
/* Dos grandes divisiones:
  * 1. Si es una perra entonces mostrarle los perros que quieren hablar con ella, para que asi ella les pueda contestar.
  * 1.1 Los perros tienen su interfaz de conecte, pero las perras necesitan una (invertida) de la de los perros.
  * 2. Si es un perro entonces mostrarle las perras que quieren hablar con el, para que asi el les pueda contestar.
  * 2.1 Se utiliza la interfaz de conectar.
*/

if (!sesion::iniciado())
{
    echo '<p>Debe estar iniciado para ver sus mensajes</p>';
    echo '<script>$(function(){window.location.href="'.PROY_URL.'";});</script>';
    return;
}

$HEAD_title = 'estas viendo tus mensajes';

function menuMensajes()
{
?>
<div>Mostrar mensajes: <input type="radio" name="filtroMensaje" class="filtroMensaje" value="noLeidos" /> No leidos / <input type="radio" name="filtroMensaje" class="filtroMensaje" value="todos" /> Todos</div>
<script type="text/javascript">
    $(function(){
        $('.filtroMensaje').click(function(){
            console.log('Seleccionado: '+$(this).val());
            $.cookie('filtroMensaje',$(this).val(),{ expires: 30, path:  '/'});
            window.location.href=window.location.href;
        });
        
        if ($.cookie('filtroMensaje') == null)
        {
            $.cookie('filtroMensaje','noLeidos',{ expires: 30, path:  '/'});
        }
        
        $('.filtroMensaje[value="'+$.cookie('filtroMensaje')+'"]').attr('checked','checked');
    });
</script>
<?php
}

if (sesion::info('tipo') == 'perra')
    mostrarMensajesPerra();
else
    mostrarMensajesPerro();

function mostrarMensajesPerro()
{
    echo '<h1>Tus chicas buenas</h1>';
    echo '<p>Aquí se encuentran todas tus chicas, si deseas hablar con alguna de ellas solo presiona el botón "Chatear", ellas pueden estar fuera de línea, pero no te preocupes que pronto verán tu mensaje y será contestado con el respectivo cariño.</p>';
    
    menuMensajes();
    
    $where = '';
    if (@$_COOKIE['filtroMensaje'] == 'noLeidos')
    {
        $where = 'AND tm.ID_origen <> '.sesion::info('ID_cuenta').' AND tm.estado = "nuevo"';
    }
    
    $c = 'SELECT cuentas.ID_cuenta, tf.ID_foto, tf.hash AS foto_hash, tm.canal, cuentas.usuario, tm.fecha_enviado, tm.mensaje, tm.ID_origen FROM credito LEFT JOIN cuentas ON cuentas.ID_cuenta=credito.desbloqueo LEFT JOIN (SELECT fotos.ID_cuenta, fotos.ID_foto, fotos.hash FROM fotos ORDER BY RAND()) AS tf ON tf.ID_cuenta=credito.desbloqueo LEFT JOIN (SELECT ID_origen, fecha_enviado, mensaje, canal, estado FROM mensajes ORDER BY fecha_enviado DESC) AS tm ON tm.canal=SHA1(CONCAT(LEAST('.sesion::info('ID_cuenta').',cuentas.ID_cuenta) , "+" , GREATEST('.sesion::info('ID_cuenta').',cuentas.ID_cuenta))) WHERE credito.ID_cuenta="'.sesion::info('ID_cuenta').'" AND credito.desbloqueo = cuentas.ID_cuenta '.$where.' GROUP BY tm.canal ORDER BY tm.fecha_enviado DESC';
    $r = db::consultar($c);
    
    if (!mysql_num_rows($r))
    {
        echo '<p>'._('No tienes mensajes nuevos').'</p>';
        return;
    }
    while ($f = mysql_fetch_assoc($r))
    {
        $url = PROY_URL.'conocer_chica_'.$f['ID_cuenta'].'_'.$f['ID_foto'].'_'.$f['usuario'].'.html';
        echo '
        <div class="listamensajes">
        <div class="listamensajes_foto"><a style="display:block;" href="'.PROY_URL.'chicas_lindas_'.$f['ID_cuenta'].'_'.$f['ID_foto'].'_'.$f['usuario'].'.html"><img src="crop_75_75_'.($f['foto_hash'] ? $f['foto_hash'] : 'sinfoto').'.jpg" /></a></div>
        <div class="listamensajes_top">'.$f['usuario'].'<br /><span style="font-size:13px;">'.stubs::timesince($f['fecha_enviado']).'</span></div>
        <div class="listamensajes_vermensaje"><a class="conectar fb" href="'.$url.'">'. '<img src="img/mensajes.gif" /> Chatear / Ver mensajes</a></div>
        <div class="listamensajes_mensaje"><img src="img/icono_msj_'.($f['ID_origen'] == sesion::info('ID_cuenta') ? 'origen' : 'remoto').'.gif" />'.stubs::ellipsis(strip_tags(($f['mensaje'])),50).'</div>
        </div>
        '."\n";
    }
}

function mostrarMensajesPerra()
{
    echo '<h1>Chicos interesados en tí</h1>';
    echo '<p>Recuerda que ganas mucho dinero conversando con ellos, por lo que procura estar pendiente de nuevos mensajes!. Cada vez que alguien te agregue como contacto para hablar contigo, nosotros te pagamos USD $0.50!.</p>';
    menuMensajes();
       
    if (@$_COOKIE['filtroMensaje'] == 'noLeidos')
    {
        $where = 'AND tm2.estado = "nuevo"';
    }

    $c = 'SELECT usuario, foto AS "foto_hash", tm2.mensaje, tm2.fecha_enviado, mensajes.ID_origen, tm2.ID_origen AS ID_origen2, tm2.estado FROM mensajes LEFT JOIN cuentas ON cuentas.ID_cuenta=mensajes.ID_origen LEFT JOIN (SELECT ID_origen, fecha_enviado, mensaje, canal, estado FROM mensajes ORDER BY fecha_enviado DESC) AS tm2 ON tm2.canal = mensajes.canal WHERE ID_destino="'.sesion::info('ID_cuenta').'" '.$where.' GROUP BY ID_origen ORDER BY fecha_enviado DESC';
    $r = db::consultar($c);
    
    if (!mysql_num_rows($r))
    {
        echo '<p>'._('No tienes mensajes nuevos').'</p>';
        return;
    }
    while ($f = mysql_fetch_assoc($r))
    {
        $url = PROY_URL.'conocer_chico_'.$f['ID_origen'].'_'.$f['usuario'].'.html';
            
        echo '<div class="listamensajes">
            <div class="listamensajes_foto"><img src="crop_75_75_'.($f['foto_hash'] ? $f['foto_hash'] : 'sinfoto').'.jpg" /></div>
            <div class="listamensajes_top">'.$f['usuario'].'<br /><span style="font-size:13px;">'.stubs::timesince($f['fecha_enviado']).'</span></div>
            <div class="listamensajes_vermensaje"><a class="conectar fb" href="'.$url.'">'. '<img src="img/mensajes.gif" /> Ver mensaje</a></div>
            <div class="listamensajes_mensaje"><img src="img/icono_msj_'.($f['ID_origen2'] == sesion::info('ID_cuenta') ? 'remoto2' : 'origen2').'.gif" />'.stubs::ellipsis(strip_tags(($f['mensaje'])),50).'</div>
            </div>
            '."\n";
    }
}

?>