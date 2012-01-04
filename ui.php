<?php
class ui
{
private static function destruir_vacios($cadena)
{
	return preg_replace("/(\s)?\w+=\"\"/","",$cadena);
}

public static function combobox ($id_gui, $opciones, $selected = "", $clase="", $estilo="") {
	$opciones = str_replace('value="'.$selected.'"', 'selected="selected" value="'.$selected.'"', $opciones);
	return '<select id="' . $id_gui . '" name="' . $id_gui . '" style="' . $estilo . '">'. $opciones . '</select>';
}

public static function input ($id_gui, $valor="", $tipo="text", $clase="", $estilo="", $extra ="") {
	$tipo = empty($tipo) ? "text" : $tipo;
	return '<input type="'.$tipo.'" id="' . $id_gui . '" name="' . $id_gui . '" class="' . $clase . '" style="' . $estilo . '" value="' . $valor .'" '.$extra.'></input>';
}

public static function textarea ($id_gui, $valor="", $clase="", $estilo="", $extra="") {
	return "<textarea id='$id_gui' name='$id_gui' class='$clase' style='$estilo' $extra>$valor</textarea>";
}

public static function optionbox_nosi ($id_gui, $valorNo = 0, $valorSi = 1, $TextoSi = "Si", $TextoNo = "No") {
	return "<input id='$id_gui' name='$id_gui' type='radio' checked='checked' value='$valorNo'>$TextoNo</input>" . '&nbsp;&nbsp;&nbsp;&nbsp;'."<input id='$id_gui' name='$id_gui' type='radio' value='$valorSi'>$TextoSi</input>";
}

public static function combobox_o_meses (){
	$opciones = '';
	for ($i = 1; $i < 13; $i++) {
		$opciones .= '<option value=$i>'.strftime('%B', mktime (0,0,0,$i,1,2009)).'</option>';
	}
	return $opciones;
}

public static function combobox_o_anios (){
	$opciones = '';
	for ($i = 0; $i < 13; $i++) {
		$opciones .= '<option value=$i>'.(date('Y') - $i).'</option>';
	}
	return $opciones;
}

public static function array_a_opciones($array)
{
	$buffer = '';
	foreach ($array as $valor => $texto)
	{
		$buffer .= '<option value="'.$valor.'">'.$texto.'</option>';
	}

	return $buffer;
}

public static function crearBotonMeGusta($ID_destino,$estado,$nombre)
{
	if ($estado)
		return '<span class="unlike" title="'.$nombre.'" rel="'.$ID_destino.'"></span>';
	else
		return '<span class="like" title="'.$nombre.'" rel="'.$ID_destino.'"></span>';
}

public static function crearRejillaContactos(array $opciones)
{
/*
	$m = new Memcached();
	$m->addServer('127.0.0.1', 11211);
    
	$buffer = $m->get(__FILE__.sha1(serialize(array(usuario::$info,$opciones))));
	
	if ($buffer)
	{
	    echo $buffer;
	    return;
	}
*/

	$contactos = usuarios::buscar($opciones);
	$buffer = '<div class="RejillaContactos">'."\n";
	
	if (!count($contactos))
	{
		if (isset($opciones['uiRejillaContactos']['noencontrado']))
			return $opciones['uiRejillaContactos']['noencontrado'];
		else
			return '<p>We could not find anyone who matches your criteria, try with different settings or another nearby location.';
	}
	
	foreach($contactos as $contacto => $detalle)
	{
		$buffer .= '<div class="RellijaContacto">'."\n".
			'<a class="conectar" href="'.PROY_URL.'contenido_conectar.html?ID='.$detalle['ID_cuenta'].'"><img class="RellijaContactoFotoPerfil" src="'.usuarios::obtenerImagen($detalle['foto_hash'],75,75,'crop_').'" /></a>'."\n".
			'<div class="RellijaContactoInfo"><a class="conectar" href="'.PROY_URL.'contenido_conectar.html?ID='.$detalle['ID_cuenta'].'"><span class="nombre">'.$detalle['nombre'].'</span></a></div>'.
			'<div class="RellijaContactoControles">
			<a class="fb conectar" href="'.PROY_URL.'contenido_conectar.html?ID='.$detalle['ID_cuenta'].'">'. '<img src="img/mensajes.gif" /> ' ._('Send message').'</a>
			'.self::crearBotonMeGusta($detalle['ID_cuenta'],$detalle['megusta'],$detalle['nombre']).'
			</div>'."\n".
		'</div>'."\n";
	}
	
	$buffer .= '</div>'."\n";

	if (isset($opciones['uiRejillaContactos']['encontrado']))
		$retornar = sprintf($opciones['uiRejillaContactos']['encontrado'],count($contactos))."\n".$buffer;
	else
		$retornar = '<p>We found '.count($contactos).' people near your location that matches your search criteria</p>'."\n".$buffer;
/*
	$m = new Memcached();
	$m->addServer('127.0.0.1', 11211);

	$m->set(__FILE__.sha1(serialize(array(usuario::$info,$opciones))), $retornar, strtotime('+1 minute'));
*/	
	return $retornar;
}

public  static function ScriptNecesariosParaConectar()
{
	return '
<script type="text/javascript" src="js/jquery.imgCenter.js"></script>
<script type="text/javascript" src="js/jquery.form.js"></script>
<script type="text/javascript" src="js/jquery.facebox.js"></script>
<link href="css/facebox.css" media="screen" rel="stylesheet" type="text/css"/>
<script type="text/javascript">
    $(document).ready(function() {
        $("a.conectar").facebox();
	$("a.conectar").click(function(){
		$("html, body").animate({ scrollTop: 0 }, "slow");
		FB.Canvas.scrollTo(0,0);
		$("#facebox").css("top","100px").fadeIn();
	});

    });
</script>';
}
}
?>
