<?php
if (!sesion::iniciado())
    return;

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
            $("#misfotos").load('<?php echo PROY_URL; ?>ajax?misfotos2');
        }
        
        $(".eliminar").live('click',function(event){event.preventDefault();$.post('ajax',{eliminar: $(this).attr('rel')},function(){cargarMisFotos();});});
        
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