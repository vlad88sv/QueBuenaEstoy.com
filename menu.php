<?php
$menu[0]['enlace'] = 'fotos';
$menu[0]['texto'] = 'Fotos';
//$menu['videos']['texto'] = 'Videos';

if (sesion::iniciado())
{
    $menu[1]['enlace'] = 'mensajes';
    $menu[1]['texto'] = 'Contactos';
    $menu[1]['tip'] = sesion::obtenerMensajesNoLeidos();
    
    if (sesion::info('tipo') == 'perra')
    {
        $menu[2]['enlace'] = 'mis_fotos';
        $menu[2]['texto'] = 'Mis fotos';
    }
}
?>
<table id="cabecera">
    <tr>
        <td id="cabecera_logo"><a href="<?php echo PROY_URL; ?>"><img src="img/logo.png" alt="Logo" /></a></td>
        <td>
        <?php if (sesion::info('tipo') != 'perro'): ?>
            <p id="cabecera_der_1">Si estas buena</p>
            <p id="cabecera_der_2">
                <a href="subir.html"><span style="font-size: 20px; color:#8d0045;">▲</span>SUBE TU FOTO</a>
            </p>
        <?php endif; ?>
        <p id="cabecera_der_3">            
        <?php if (sesion::iniciado()): ?>
            
                <a href="<?php echo PROY_URL; ?>sesion.html">Cerrar sesión</a>
                <br />
                <?php
                if (sesion::info('tipo') == 'perra')
                    echo 'Balance: '.sesion::obtenerBalance().'<br /><a href="pagar.html">Cobrar mi dinero</a>';
                else
                    echo 'Créditos: '.sesion::obtenerCreditos();
                ?>
            
        <?php else: ?>
            <a class="conectar" href="<?php echo PROY_URL; ?>iniciar.html">Iniciar sesión</a>
        <?php endif; ?>
        </p>
        </td>
    </tr>
</table>
<ul class="dropdown">
<?php
foreach ($menu as $enlace => $datos)
{
    echo '<li'.($_GET['accion'] == $datos['enlace'] ? ' class="seleccionado"' : '').'><a id="menu_'.$datos['enlace'].'"  title="'._($datos['texto']).'" href="'.PROY_URL.$datos['enlace'].'.html'.@$datos['query'].'">'._($datos['texto']).@$datos['sufijo'].'</a>'.(@$datos['tip'] ? '<span class="menu_tip">'.$datos['tip'].'</span>' : '').'</li>';
}
?>
<div style="float: right;" class="fb-like" data-href="http://www.facebook.com/pages/Quebuenaestoycom/168366446596811" data-send="true" data-layout="button_count" data-width="165" data-show-faces="false" data-font="lucida grande"></div>
</ul>
<?php
if (!empty($_COOKIE['ID_pais']))
    $paisSeleccionado = db::obtenerCampoPorIndice('datos_pais','ID_pais','pais',$_COOKIE['ID_pais']);
else
    $paisSeleccionado = 'Todos los países';

$c = 'SELECT ID_pais, pais FROM datos_pais ORDER BY pais ASC';
$r = db::consultar($c);
while ($f = mysql_fetch_assoc($r))
    $paises[$f['ID_pais']] = $f['pais'];

$c = 'SELECT ID_pais, pais FROM datos_pais WHERE ID_pais IN (SELECT ID_pais FROM cuentas WHERE verificado=1) ORDER BY pais ASC';
$r = db::consultar($c);
while ($f = mysql_fetch_assoc($r))
    $paisesConFotos[$f['ID_pais']] = $f['pais'];
?>
<?php if ($_GET['accion'] == 'fotos'): ?>
<div id="pais_seleccionado">
    Viendo fotos de <span class="pais_seleccionado"><?php echo $paisSeleccionado; ?></span><a id="cambiarPais">▼</a>
    <form id ="buscar" action="<?php echo PROY_URL; ?>" method="post">
        Buscar por nombre o nickname:
        <input type="text" name="buscar" value="" />
        <input type="image" src="img/boton_buscar.png" />
    </form>
</div>
<hr style="border:none;height: 1px;background-color:#DDD;color:#DDD;"/>
<div id="ordenarPor">
    <span>Ordernar por: </span><a class="ordenarPor" rel="nuevas" href="#">Más nuevas</a><a class="ordenarPor" rel="vistas" href="#">Más vistas</a><a class="ordenarPor" rel="rating" href="#">+Hot</a><a class="ordenarPor" rel="aleatorio" href="#">Aleatorio</a>
</div>
<script>
    $(function(){
        $('#cambiarPais').click(function(){$('.pais_seleccionado').html('<?php echo ui::combobox('ID_pais',ui::array_a_opciones(array_merge(array('' => 'Cualquier país'),$paisesConFotos)),@$_COOKIE['ID_pais']); ?>');});
        $('span.pais_seleccionado #ID_pais').live('change',function(){$.cookie('ID_pais', $("#ID_pais :selected").val(), { expires: 30, path:  '/'});window.location.href=window.location.href.split("?")[0];});
        $('.ordenarPor').click(function(event){event.preventDefault();$.cookie('ordenarPor', $(this).attr('rel'), { expires: 30, path:  '/'});window.location.href=window.location.href.split("?")[0];});
        $('a.ordenarPor[rel="'+($.cookie('ordenarPor') == null ? "aleatorio" : $.cookie('ordenarPor'))+'"]').addClass("seleccionado");
    });
</script>
<?php endif; ?>