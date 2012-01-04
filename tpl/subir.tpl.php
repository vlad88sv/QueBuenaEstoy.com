<?php
if (sesion::iniciado())
{
    if (sesion::info('tipo') != 'perra')
    {
        echo '<script>$(function(){window.location.href="'.PROY_URL.'";});</script>';
        return;
    }
    
    if (sesion::info('verificado') == '0')
    {
        echo '<p style="font-size:14px;color:#F00;">Hemos enviado un correo de confirmación a tu correo, tus fotografías serán publicadas hasta que confirmes tu correo.</p>';
    }
?>
<h1>Mis fotos</h1>
<div id="controles_fotos">
    <img src="crop_100_100_sinfoto.jpg" id="foto_nueva" />
    <div class="cargar-archivo">Subir foto</div>
</div>
<div id="misfotos"></div>
<script>
    $(function(){
        function cargarMisFotos()
        {
            $("#misfotos").load('<?php echo PROY_URL; ?>ajax?misfotos');
        }
        
        $(".eliminar").live('click',function(){$.post('ajax',{eliminar: $(this).attr('rel')},function(){cargarMisFotos();});});
        
        cargarMisFotos();
        
        $('.cargar-archivo').each(function(){
            new qq.FileUploaderBasic({
                button: $(this)[0],
                identificador: this,
                action: '<?php echo PROY_URL; ?>carga',
                showMessage: function(message){ alert(message); },
                debug: false,
                allowedExtensions: ['jpg', 'png', 'jpeg', 'gif'],
                onSubmit: function(id, fileName){$('#foto_nueva').attr('src','img/cargando.gif');},
                onProgress: function(id, fileName, loaded, total){},
                onComplete: function(id, fileName, responseJSON){cargarMisFotos();$('#foto_nueva').attr('src','crop_100_100_sinfoto.jpg');}
            });
        });
    });
</script>
<?php
    return;
}
?>
<p>Si estas bien buena entonces registrate y luego carga tu foto  con este formulario, luego revisa tu correo para confirmar.</p>
<p>Tu correo permanecerá oculto para el público. Tu recibirás USD $0.50 por cada chico que te quiera contactar!. Podrás corresponder los mensajes únicamente por medio del centro de Contactos de QueBuenaEstoy.com (por medio del menú <strong>contactos</strong>).</p>
<p>Si eres hombre favor haz clic aquí: <a class="conectar" href="iniciar.html">Inicio de sesión para hombres</a></p>
<?php
if (isset($_POST['accion']))
{
    $errores = array();
    if ($_POST['accion'] == 'identificar')
    {
        $c = 'SELECT COALESCE(COUNT(*),0) AS "encontrado" FROM cuentas WHERE correo = "'.db::codex($_POST['lcorreo']).'" AND clave = SHA1("'.db::codex($_POST['lclave']).'")';
        $r = db::consultar($c);
        $f = mysql_fetch_assoc($r);
        if ($f['encontrado'] > 0) {
            sesion::iniciar('correo',$_POST['lcorreo']);
            
            if (is_array($_FILES['archivo']) && isset($_FILES['archivo']['error']) && $_FILES['archivo']['error'] == 0)
            {
                $hash = sha1(microtime(true));
                move_uploaded_file($_FILES['archivo']['tmp_name'],'pool/img/'.$hash);
                db::insertar('fotos',array('hash' => "'".$hash."'", 'ID_cuenta' => sesion::info('ID_cuenta'), 'creacion' => 'CURRENT_TIMESTAMP'),true);
            }
            
            if (sesion::info('tipo') == 'perra')
            {
                echo '<script>$(function(){window.location.href=window.location.href;});</script>';
                return;
            }   
            else
            {
                echo '<script>$(function(){window.location.href="'.PROY_URL.'";});</script>';
                return;
            }
        } else {
            echo '<div class="error_burbuja">';
            echo '<p>Hemos detectado los siguientes errores al iniciar sesión:</p>';
            echo '<ul><li>Correo o contraseña inválidos</li></ul>';
            echo '</div>';
        }
    }
    
    if ($_POST['accion'] == 'registrar')
    {
        
        // Corroborar primero si no existe la cuenta
        
        if (db::obtenerPorIndice('cuentas','correo',array($_POST['correo'])))
        {
            $errores[] = 'ya existe este correo';
        }

        if (!is_array($_FILES['archivo']) || @$_FILES['archivo']['error'] != 0)
        {
            $errores[] = 'no seleccionó ningún archivo';
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
            echo '<div class="error_burbuja">';
            echo '<p>Hemos detectado los siguientes errores en los datos introducidos y no podremos procesar su registro a menos que sean corregidos:</p>';
            echo '<ul><li>'.join('</li><li>',$errores).'</li></ul>';
            print_r($errores);
            echo '</div>';
        } else {
            $hash = sha1(microtime(true));
            $hashUsuario = sha1(microtime(true)); 
            move_uploaded_file($_FILES['archivo']['tmp_name'],'pool/img/'.$hash);
            
            unset($datos);        
            $datos['correo'] = "'".db::codex($_POST['correo'])."'";
            $datos['clave'] = "'".sha1($_POST['clave'])."'";
            $datos['ID_pais'] = "'".db::codex($_POST['ID_pais'])."'";
            $datos['usuario'] = "'".db::codex(strip_tags($_POST['usuario']))."'";
            $datos['creacion'] = 'CURRENT_TIMESTAMP';
            $datos['hash'] = "'".$hashUsuario."'";
            $ID_cuenta = db::insertar('cuentas',$datos,true);
            unset($datos);
            
            db::insertar('fotos',array('hash' => "'".$hash."'", 'ID_cuenta' => $ID_cuenta, 'creacion' => 'CURRENT_TIMESTAMP'),true);
    
            $enlace = PROY_URL.'confirmar.html?email='.$_POST['correo'].'&hash='.$hashUsuario;
            $mensaje = '<p>QueBuenaEstoy.com:</p>
            <p>Bienvenida a quebuenaestoy.com, para confirmar tu cuenta haz clic aquí:</p>
            <a href="'.$enlace.'">'.$enlace.'</a>';
            stubs::correo(@$_POST['correo'],'Confirmación de correo de QueBuenaEstoy.com',$mensaje); 
            
            sesion::iniciar('correo',@$_POST['correo']);
            
            // Enviemos correo de bienvenida.
            
            $body = '<h1>Bievenida a QueBuenaEstoy.com</h1><p>Gracias por su registro en <strong>quebuenaestoy.com</strong>, a partir de ahora podrá subir nuevas fotos o eliminar las actuales en la sección <a href="'.PROY_URL.'subir.html">Subir Fotos</a> y podrá ver que opina la gente de sus fotos en <a href="'.PROY_URL.'mis_fotos.html">Mis fotos</a> y ganar mucho dinero mientras conoce chicos en la sección <a href="'.PROY_URL.'mensajes.html">Mensajes</a></p>';
            stubs::correo($_POST['correo'],'Bievenida a QueBuenaEstoy.com',$body);

            db::insertar('mensajes',array('ID_origen' => 1, 'ID_destino' => $ID_cuenta, 'mensaje' => $body, 'canal' => sha1('1+'.$ID_cuenta) ));
            echo '<script>$(function(){window.location.href=window.location.href;});</script>';
        }
    }
}
?>
<table id="sesion" style="table-layout: fixed;">
<tr><td id="registrate" style="width: 50%;">
<h1>Soy nueva</h1>
<form class="fsesion" action="/subir.html" method="post" enctype="multipart/form-data">
<input type="hidden" name="accion" value="registrar" />
<table>
    <tr><td>Nick</td><td><input type="text" name="usuario" value="<?php echo @$_POST['usuario']; ?>" /></td></tr>
    <tr><td>Correo</td><td><input type="text" name="correo" value="<?php echo @$_POST['correo']; ?>" /></td></tr>
    <tr><td>País</td><td><?php echo ui::combobox('ID_pais',ui::array_a_opciones($paises),@$_POST['ID_pais']); ?></td></tr>
    <tr><td>Contraseña</td><td><input type="password" name="clave" value="" /></td></tr>
    <tr><td>Foto</td><td><input type="file" name="archivo" value="" /></td></tr>
</table>
<input type="checkbox" name="privacidad" class="privacidad" rel="boton_registrar" value="1" /><label for="privacidad">Certifico que las fotografías en mi cuenta representan exclusivamente a mi persona y publicarla no viola ninguna ley de mi país.</label>
<div><input type="submit" id="boton_registrar" name="registrar" value="Registrarme" /></div>
</form>
</td><td>
<h1>Ya tengo cuenta</h1>
<form class="fsesion" action="/subir.html" method="post" enctype="multipart/form-data">
<input type="hidden" name="accion" value="identificar" />
<table>
    <tr><td>Correo</td><td><input type="text" name="lcorreo" value="<?php echo @$_POST['lcorreo']; ?>" /></td></tr>
    <tr><td>Contraseña</td><td><input type="password" name="lclave" value="" /></td></tr>
    <tr><td>Foto (opcional)</td><td><input type="file" name="archivo" value="" /></td></tr>
</td></tr>
</table>
<div><input type="submit" name="sesion" id="boton_sesion" value="iniciar sesión" /></div>
</form>
</table>
<script>
    $(function(){
        $('#boton_registrar').click(function(event){
            if ( $('input[rel="'+$(this).attr('id')+'"]').is(':checked') == false)
            {
                event.preventDefault();
                alert('Certifique que la fotografía es suya.');
                return;
            }
        });
    });
</script>