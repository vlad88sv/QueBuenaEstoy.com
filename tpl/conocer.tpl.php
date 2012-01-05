<?php
/* Procesar login/registro */

if (isset($_POST['accion']) && $_POST['accion'] == 'registrar')
{
    // Corroborar primero si no existe la cuenta
    $errores = array();
        
    if ( db::obtenerPorIndice('cuentas','correo',array($_POST['correo'])) )
    {
        $errores[] = 'ya existe este correo';
    }

    if (empty($_POST['clave']))
    {
        $errores[] = 'no ingresó clave';
    }
    
    if (empty($_POST['usuario']) || strlen($_POST['usuario']) < 4)
    {
        $errores[] = 'debe ingresar un nick de al menos 4 letras';
    }

    if (count($errores) > 0)
    {
        echo '<h1>Lo sentimos, hay errores en los datos ingresados</h1>';
        echo '<p>Hemos detectado los siguientes errores en los datos introducidos y no podremos procesar su registro a menos que sean corregidos:</p>';
        echo '<p class="error">'.join('</p><p class="error">',$errores).'</p>';
    } else {
        $datos['clave'] = sha1($_POST['clave']);
        $datos['correo'] = $_POST['correo'];
        $datos['usuario'] = strip_tags($_POST['usuario']);
        $datos['tipo'] = 'perro';
        $ID_cuenta = db::insertar('cuentas',$datos,false);
        
        sesion::iniciar('correo',$_POST['correo']);
        $body = '<h1>Bievenido a QueBuenaEstoy.com</h1><p>Gracias por su registro en <strong>quebuenaestoy.com</strong>, a partir de ahora podrá hacer comentarios publicos y conocer miles de chicas <strong>reales</strong> en <a href="'.PROY_URL.'">QueBuenaEstoy.com</a> y podrá ver que comenta la gente de las todas las chicas</p>';
        stubs::correo($_POST['correo'],'Bievenido a QueBuenaEstoy.com',$body);

        db::insertar('mensajes',array('ID_origen' => 1, 'ID_destino' => $ID_cuenta, 'mensaje' => $body, 'canal' => sha1('1+'.$ID_cuenta)));
        db::insertar('credito',array('ID_cuenta' => $ID_cuenta, 'creditos' => 1));
        db::insertar('credito',array('ID_cuenta' => $ID_cuenta, 'desbloqueo' => '1', 'creditos' => '-1'));
        
        return;
    }
}

if (isset($_POST['accion']) && $_POST['accion'] == 'identificar')
{
    $c = 'SELECT COALESCE(COUNT(*),0) AS "encontrado" FROM cuentas WHERE correo = "'.db::codex($_POST['correo']).'" AND clave = SHA1("'.db::codex($_POST['clave']).'")';
    $r = db::consultar($c);
    $f = mysql_fetch_assoc($r);
    if ($f['encontrado'] > 0) {
        sesion::iniciar('correo',$_POST['correo']);
        echo 'OK';
    } else {
        echo 'ERROR';
    }
    return;
}


if (sesion::iniciado() && isset($_POST['SoyHombre']))
{
    $c = 'INSERT INTO credito (creditos, ID_cuenta,desbloqueo) VALUES (-1,'.sesion::info('ID_cuenta').',"'.db::codex($_GET['ID_cuenta']).'")';
    $r = db::consultar($c);
    return;
}

/* Cinco casos:
  * 0. Si es perra, vino por los mensajes, ya esta logeada (pero verificar) y mostrarle chat!.
  * 1. No logeado: ofrecerle registro/login
  * 2. Logeado pero sin créditos: ofrecerle comprar créditos
  * 3. Logeado y con créditos: ofrecerle desbloquear a la chica
  * 4. Logeado y desbloqueada: mostrarle el chat!
*/

if (sesion::iniciado() && sesion::info('tipo') == 'perra' && isset($_GET['ID_cuenta']) && is_numeric($_GET['ID_cuenta']) && empty($_GET['ID_foto']))
{
    MostrarChatPerra();
    return;
}

if (!sesion::iniciado())
    OfrecerRegistroLogin();
elseif (sesion::iniciado() && sesion::laTieneDesbloqueada($_GET['ID_cuenta']))
    MostrarChat();
elseif (sesion::iniciado() && (sesion::obtenerCreditos() == 0))
    OfrecerComprarCreditos();
elseif (sesion::iniciado() && !sesion::laTieneDesbloqueada($_GET['ID_cuenta'])) 
    OfrecerDesbloquear();

function OfrecerRegistroLogin()
{
?>
<div style="text-align: center;">
<h1>Ya tengo mi cuenta</h1>
<p>Válido para inicio de sesión de <span style="color:#00F;">chicos</span> y <span style="color:#F00;">chicas</span></p>
<form id="inicio_sesion" class="ajaxer" action="<?php echo PROY_URL_ACTUAL; ?>" method="post">
<input type="hidden" name="accion" value="identificar" />
<table style="width:265px;margin:auto;">
    <tr><td>Correo</td><td><input type="text" name="correo" value="" /></td></tr>
    <tr><td>Contraseña</td><td><input type="password" name="clave" value="" /></td></tr>
</td></tr>
</table>
<div><input style="padding: 2px;color:#A0004D;" type="submit" name="sesion" value="iniciar sesión" /></div>
</form>
</div>
<hr />
<div style="text-align: center;">
<table id="sesion">
<tr>
<td>
<h1>Soy nuevo</h1>
<p>Válido para registro de <span style="color:#00F;">chicos</span></p>
<form class="ajaxer" action="<?php echo PROY_URL_ACTUAL; ?>" method="post">
<input type="hidden" name="accion" value="registrar" />
<table>
    <tr><td>Nick</td><td><input type="text" name="usuario" value="<?php echo @$_POST['usuario']; ?>" /></td></tr>
    <tr><td>Correo</td><td><input type="text" name="correo" value="<?php echo @$_POST['usuario']; ?>" /></td></tr>
    <tr><td>Contraseña</td><td><input type="password" name="clave" value="<?php echo @$_POST['clave']; ?>" /></td></tr>
</table>
<div><input type="submit" name="registrar" value="Registrarme" /></div>
</form>
</div>
</td>
<td>
<h1>Soy nueva</h1>
<p>Válido para registro de <span style="color:#F00;">chicas</span></p>
<a href="subir.html">Haz clic aquí para ir al registro de chicas</a>
</td>
</tr>
</table>

<script>
    $(function(){
        $('form').submit(function(event){
            event.preventDefault();
            $.post('<?php echo PROY_URL_ACTUAL; ?>',
                $(this).serialize(),
                 function(data){
                  if (data == "OK")
                  {
                      if (<?php echo (isset($_GET['SI']) ? 1 : 0); ?>) {
                          window.location.href=window.location.href;
                      } else {
                          window.location.href="<?php echo PROY_URL.'?fbox='.PROY_URL_ACTUAL; ?>";
                      }
                  } else {
                      alert("Datos de ingreso incorrectos"); $("#inicio_sesion")[0].reset();
                  }
                }
            );
        });
    });
</script>
<?php
}

function OfrecerComprarCreditos()
{
?>
<p>Antes de contactar a esta linda chica necesitas créditos, demuestrale que no eres un cualquiera.</p>
<p>Cada chica cuesta 1 crédito, por lo que 10 créditos te servirán para 10 chicas y asi sucesivamente.</p>
<p>Nota: será redirigido a la interfaz de pago seguro de <b>PayPal</b>. <span style="font-color:#F00;">Utilice el mismo correo que utilizó para registrarse en este sitio.</span></p>
<div style="text-align: center;">
<table style="margin: auto;width: 350px;">
    <tr><td>10 créditos</td><td>$10.00</td><td><form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="R2MJRJG9VS6B8"><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"><img alt="" border="0" src="https://www.paypalobjects.com/es_XC/i/scr/pixel.gif" width="1" height="1"></form></td></tr>
    <tr><td>50 créditos</td><td>$40.00</td><td><form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="PJ5ED4N7NBJFY"><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"><img alt="" border="0" src="https://www.paypalobjects.com/es_XC/i/scr/pixel.gif" width="1" height="1"></form></td></tr>
    <tr><td>100 créditos</td><td>$70.00</td><td><form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="R37TJ3KRM9ANU"><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"><img alt="" border="0" src="https://www.paypalobjects.com/es_XC/i/scr/pixel.gif" width="1" height="1"></form></td></tr>
</table>
</div>
<?php
}

function OfrecerDesbloquear()
{
    $c = 'SELECT hash FROM fotos WHERE ID_foto='.$_GET['ID_foto'];
    $r = db::consultar($c);
    $f = mysql_fetch_assoc($r);
?>
<p style="text-align:center;">Podrás hablar con esta linda chica por un 1 mes, te costará <b>1</b> crédito. ¿Aceptas?</p>
<div style="text-align: center;">
    <img src="imagen_200_200_<?php echo $f['hash']; ?>.jpg">
</div>
<div style="text-align: center;">
    <form action="<?php echo PROY_URL_ACTUAL; ?>" method="post">
        <input type="hidden" name="SoyHombre" value="si" />
        <input type="submit" value="Si!, me gustaría conocerla" />
        <input type="button" onclick="jQuery(document).trigger('close.facebox');" id="marica" value="Mejor no, me dan pena las mujeres" />
    </form>
</div>
<script>
    $(function(){
        $('form').submit(function(event){
            event.preventDefault();
            $.post('<?php echo PROY_URL_ACTUAL; ?>',$(this).serialize(),function(){$.facebox({ajax: '<?php echo PROY_URL_ACTUAL; ?>'});});
        });
    });
</script>
<?
}

function MostrarChat()
{
    $canal = sha1(min(sesion::info('ID_cuenta'),$_GET['ID_cuenta']).'+'.max(sesion::info('ID_cuenta'),$_GET['ID_cuenta']));
    
    $c = 'SELECT fotos.hash,usuario FROM fotos LEFT JOIN cuentas USING(ID_cuenta) WHERE ID_foto='.$_GET['ID_foto'];
    $r = db::consultar($c);
    $f = mysql_fetch_assoc($r);
?>

<script type="text/javascript">
    ultimaActualizacion = 0;
    actualizando = false;
    $(document).bind('afterClose.facebox', function() { window.clearInterval(IDInterval); });        
    
    function cargarMensajes() {
        if (actualizando) return;
        actualizando = true;
        $("#mensajes").load('<?php echo PROY_URL. "ajax?ajax=ver_mensajes&desde='+ultimaActualizacion+'&canal=". $canal; ?>', function() {
            $('#mensajes').scrollTo('max');
            ultimaActualizacion =  Math.round(new Date().getTime() / 1000);
            actualizando = false;
        });
    }
    
    IDInterval = window.setInterval(cargarMensajes,1000);
    
    $(document).ready(function() {
        $('#ajax_mensajes').submit(function(event) {
            event.preventDefault();
            $.post('<?php echo PROY_URL; ?>ajax',$('#ajax_mensajes').serialize($('#ajax_mensajes')));
            $('#mensaje').val('');
        });
        
        $('#enviar').click(function () {$('#ajax_mensajes').submit();});
        
        $('.cargar-archivo').each(function(){
            new qq.FileUploaderBasic({
                button: $(this)[0],
                identificador: this,
                action: '<?php echo PROY_URL.'carga'; ?>',
                showMessage: function(message){ alert(message); },
                debug: false,
                allowedExtensions: ['jpg', 'png', 'jpeg', 'gif'],
                onSubmit: function(id, fileName){$('#foto').attr('src','img/cargando.gif');},
                onProgress: function(id, fileName, loaded, total){},
                onComplete: function(id, fileName, responseJSON){$('#foto').attr('src','crop_100_100_'+responseJSON.hash+'.jpg');}
            });
        });
    });        
</script> 
<div id="contenedor-areas">
    <div id="area-mensajes">
        <div id="mensajes">
        </div>
        <div id="redaccion-mensajes">
            <form id="ajax_mensajes" method="post" action="<?php echo PROY_URL; ?>ajax">
                <input type="hidden" name="ID_destino" value="<?php echo $_GET['ID_cuenta']; ?>" />
                <input type="text" id="mensaje" name="mensaje" value="" />
                <a class="fb" href="javascript:void(0)" id="enviar"><img src="img/mensajes.gif" />Enviar mensaje</a>
            </form>
        </div>
    </div>
    <div id="area-informacion">
        <p>Estas chateando con <?php echo $f['usuario']; ?></p>
        <img src="<?php echo 'crop_100_100_'.$f['hash'].'.jpg'; ?>" />
        <hr />
        <p style="text-align:right;"><?php echo sesion::info('usuario'); ?>, así es como te ve ella a tí:</p>
        <div style="width: 100px;float:right;">
        <img id="foto" style="width:100px;height: 100px;" src="crop_100_100_<?php echo (sesion::info('foto')== '' ? 'sinfoto' : sesion::info('foto')); ?>.jpg" /><br />
        <div class="cargar-archivo">Cambiar</div>
        </div>
    </div>
</div>
<?php
}

function MostrarChatPerra()
{
    $canal = sha1(min(sesion::info('ID_cuenta'),$_GET['ID_cuenta']).'+'.max(sesion::info('ID_cuenta'),$_GET['ID_cuenta']));
    
    $c = 'SELECT foto,usuario FROM cuentas WHERE ID_cuenta='.$_GET['ID_cuenta'];
    $r = db::consultar($c);
    $f = mysql_fetch_assoc($r);
?>
<script type="text/javascript">
    ultimaActualizacion = 0;
    $(document).bind('afterClose.facebox', function() { window.clearInterval(IDInterval); });
    actualizando = false;
    
    function cargarMensajes() {
        if (actualizando) return;
        actualizando = true;
        $("#mensajes").load('<?php echo PROY_URL. "ajax?ajax=ver_mensajes&desde='+ultimaActualizacion+'&canal=". $canal; ?>', function() {
            $('#mensajes').scrollTo('max');
            ultimaActualizacion =  Math.round(new Date().getTime() / 1000);
            actualizando = false;
        });
    }
    
    IDInterval = window.setInterval(cargarMensajes,1000);
    
    cargarMensajes();
    
    $(document).ready(function() {
        $('#ajax_mensajes').submit(function(event) {
            event.preventDefault();
            $.post('<?php echo PROY_URL; ?>ajax',$('#ajax_mensajes').serialize($('#ajax_mensajes')));
            $('#mensaje').val('');
        });
        
        $('#enviar').click(function () {$('#ajax_mensajes').submit();});
    });
</script> 
<div id="contenedor-areas">
    <div id="area-mensajes">
        <div id="mensajes">
        </div>
        <div id="redaccion-mensajes">
            <form id="ajax_mensajes" method="post" action="<?php echo PROY_URL; ?>ajax">
                <input type="hidden" name="ID_destino" value="<?php echo $_GET["ID_cuenta"]; ?>" />
                <input type="text" id="mensaje" name="mensaje" value="" />
                <a class="fb" href="javascript:void(0)" id="enviar"><img src="img/mensajes.gif" /> <?php echo _('Enviar mensaje'); ?></a>
            </form>
        </div>
    </div>
    <div id="area-informacion">
        <p>Estas chateando con <?php echo $f['usuario']; ?></p>
        <img src="crop_100_100_<?php echo ($f['foto'] ? $f['foto'] : 'sinfoto').'.jpg'; ?>" />
        <hr />
        <p>Recuerda: diviertete chateando con él y responde tan pronto como puedas. Evita dar información privada (teléfono, dirección de casa o trabajo, número de identificación personal o seguro social).</p>
        <p>Ganas mas dinero si te hablan muchas personas, así que procura ser interesante para que el te refiera a sus amigos!.</p>
    </div>
</div>
<?php
}
?>