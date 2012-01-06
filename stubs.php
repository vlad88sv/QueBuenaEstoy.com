<?php
class stubs
{
    /**
     * Formats portion of the WHERE clause for a SQL statement.
     * SELECTs points within the $distance radius
     *
     * @param float $lat Decimal latitude
     * @param float $lon Decimal longitude
     * @param float $distance Distance in kilometers
     * @return string
     */
    public static function mysqlHaversine($lat = 0, $lon = 0)
    {
        $dLat = "($lat-`lat`)";
        $dLon = "($lon-`lon`)";
        
        $a = "(SIN($dLat/2) * SIN($dLat/2) + COS($lat) *
        COS(`lat`) * SIN($dLon/2) * SIN($dLon/2))";
        $c = "(2 * ASIN(SQRT($a)))";
        $d = "(6372.797 * $c)";
        return $d;

    }// "mysqlHaversine" function

    public static function crearHash40SHA1()
    {
        return sha1(microtime(true));
    }
    
    public static function crear_imagen($origen,$destino,$ancho,$alto)
    {    
        if(@($ancho*$alto) > 617500)
            die('La imagen solicitada excede el límite de este servicio');
    
        $origen = 'pool/img/'.$origen;
        $destino = 'pool/img/m/'.$destino.'.jpg';
        
        if (!file_exists($destino))
        {
	    require_once('phmagick/phmagick.php');
	    $phMagick = new phMagick ($origen, $destino);
	    $phMagick->resize($ancho,$alto,false);
	   
	    if (!file_exists('pool/img/wm_text.png'))
	    {
		$format = new phMagickTextObject();
		$format->fontSize(10)->font('Arial.ttf')->color('#000')->background('#f0f0f0');
		$wphMagick = new phMagick('', 'pool/img/wm_text.png');
		$wphMagick->fromString(html_entity_decode('QueBuenaEstoy.com'), $format);
	    }
	
	    $phMagick->watermark('pool/img/wm_text.png', phMagickGravity::SouthEast, 50);
        }
    
        header("Accept-Ranges: bytes",true);
        header("Content-Length: ".filesize($destino),true);
        header("Keep-Alive: timeout=15, max=100",true);
        header("Connection: Keep-Alive",true);
        header("Content-Type: image/jpeg",true);
        
        readfile($destino);
    }
  
    public static function crop_imagen($origen,$destino,$ancho)
    {    
        if(@($ancho*$alto) > 562500)
            die('La imagen solicitada excede el límite de este servicio');
    
        $origen = 'pool/img/'.$origen;
        $destino = 'pool/img/c/'.$destino.'.jpg';
        
        if (!file_exists($destino))
        {
	   require_once('phmagick/phmagick.php');
           $phMagick = new phMagick ($origen, $destino);
           $phMagick->resizeExactly($ancho,$ancho);
        }
    
        header("Accept-Ranges: bytes",true);
        header("Content-Length: ".filesize($destino),true);
        header("Keep-Alive: timeout=15, max=100",true);
        header("Connection: Keep-Alive",true);
        header("Content-Type: image/jpeg",true);
    
        readfile($destino);

    }
    
    public static function guardar_imagen_cargada($variable)
    {
        if (!isset($_FILES[$variable]['tmp_name']))
            return;
        
        $hash = stubs::crearHash40SHA1();
        if (move_uploaded_file($_FILES[$variable]['tmp_name'],'pool/img/'.$hash))
            return $hash;
        else
            return false;
        
    }
    
    public static function correo ($para, $asunto, $mensaje)
    {
	$headers = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=UTF-8' . "\r\n" . 'Date: '.date("r") . "\r\n";
	$headers .= 'From: "'. general::$config['smtp_nombre'] .'" <'. general::$config['smtp_correo'] . ">\r\n";
	
	if (!empty($exHeaders))
	{
	    $headers .= $exHeaders;
	}
	return mail($para,'=?UTF-8?B?'.base64_encode($asunto).'?=',$mensaje,$headers);

    }
    
    /*
    public static function correo ($para, $asunto, $mensaje)
    {
	require_once('PHPMailer/class.phpmailer.php');
	$Mail               		= new PHPMailer();
	$Mail->IsHTML       	(true) ;
	$Mail->SetLanguage  ("es", 'PHPMailer/');
	$Mail->PluginDir	= 'PHPMailer/';
	$Mail->Mailer		= 'smtp';
	$Mail->Host		= "smtp.gmail.com";
	$Mail->SMTPSecure   = "ssl";
	$Mail->Port		= 465;
	$Mail->SMTPAuth	= true;
	$Mail->Username	= general::$config['smtp_usuario'];
	$Mail->Password	= general::$config['smtp_clave'] ;
	$Mail->CharSet		= "utf-8";
	$Mail->Encoding	= "quoted-printable";
	$Mail->SetFrom		(general::$config['smtp_correo'], general::$config['smtp_nombre']);
	$Mail->Subject		= $asunto;
	$Mail->Body		= $mensaje;
    
	$correos = preg_split('/,/',$para);
	foreach($correos as $correo)
	    $Mail->AddAddress ($correo);
    
	$x = $Mail->Send();
	
	if ($x)
	   return $x;
	else
	   return 0;
    }
    */

    public static function CrearRejilla($ops = array())
    {
	global $paises;
	global $HEAD_title;
	
	// Todos los datos del filtro de obtienen de las cookies
	$where = null;
	$awhere = null;
	$orderBy = null;
		
	if (!empty($_POST['buscar']))
	{
	    echo '<h1>Búsqueda</h1>';
	    echo '<p>Búscando por nickname <strong>'.strip_tags($_POST['buscar']).'</strong> en <strong>'.( isset($paises[@$_COOKIE['ID_pais']]) ? $paises[$_COOKIE['ID_pais']] : 'todo el mundo').'</strong>';
	    $awhere['cuentas.usuario'] = '"'.db::codex($_POST['buscar']).'"';
	    $HEAD_title = 'buscando mujeres bonitas en tu país';
	} else {
	    switch (@$_COOKIE['ordenarPor'])
	    {
		case 'vistas':
		    $tipoViendo = 'mas vistas';
		    break;
		case 'rating':
		    $tipoViendo = 'mas hot';
		    break;
		case 'nuevas':
		    $tipoViendo = 'mas recientes';
		    break;
		case 'aleatorio':
		default:
		    $tipoViendo = 'mas impresionantes';
		    break;
	    }
	    $HEAD_title = 'viendo las fotos '.$tipoViendo.' de '.( isset($paises[@$_COOKIE['ID_pais']]) ? $paises[$_COOKIE['ID_pais']] : 'todos los paises');
	}
	
	
	if (!empty($_COOKIE['ID_pais']) && !isset($_GET['mias']))
	    $awhere['ID_pais'] = '"'.db::codex($_COOKIE['ID_pais']).'"';
	    
	if (isset($_GET['mias']))
	    $awhere['cuentas.ID_cuenta'] = '"'.sesion::info('ID_cuenta').'"';

	if (!isset($_GET['sinaprobar']))
	{
	    $awhere['verificado'] = 1;
	}
	
	if (count($awhere))
	{
	    foreach ($awhere as $campo => $valor)
		$where .= " AND $campo = $valor";
	}

	if (sesion::iniciado() && isset($_GET['misfavoritas']))
	    $where .= ' AND ID_foto IN (SELECT desbloqueo FROM credito WHERE ID_cuenta="'.sesion::info('ID_cuenta').'")';
	    
	if (isset($_GET['p']) && is_numeric($_GET['p']))
	    $offset = $_GET['p']*40;
	else
	    $_GET['p'] = $offset = 0;

	switch (@$_COOKIE['ordenarPor'])
	{
	    case 'vistas':
		$orderBy = 'cantidad_vistas DESC';
		break;
	    case 'rating':
		$orderBy = 'rating_promedio DESC, cantidad_votos DESC';
		break;
	    case 'nuevas':
		$orderBy = 'creacion DESC';
		break;
	    case 'aleatorio':
	    default:
		$orderBy = 'RAND()';
	}
	$orderBy = 'ORDER BY '.$orderBy;
	
	$c = 'SELECT cuentas.ID_cuenta, tf.creacion, tf.ID_foto, tf.hash AS "foto_hash", (SELECT COUNT(*) FROM fotos AS tf2 WHERE tf2.ID_cuenta = tf.ID_cuenta) AS cantidad_fotos, `pais`, COALESCE(cantidad_votos_a,0) AS cantidad_votos, FORMAT(COALESCE(rating_promedio_a,0),1) AS "rating_promedio", cantidad_vistas, tf.creacion, usuario FROM fotos as tf LEFT JOIN cuentas USING(ID_cuenta) LEFT JOIN (SELECT ID_foto, COUNT(*) AS "cantidad_votos_a", AVG(rating) AS "rating_promedio_a" FROM votos GROUP BY ID_foto) AS tv USING(ID_foto) LEFT JOIN datos_pais USING(ID_pais) WHERE `cuentas`.`tipo` = "perra" '.$where .' '.$orderBy . ' LIMIT '.$offset.',40';

	/* Nuestro cache principal se basa en la lógica de que para cada $c hay un solo resultado, sin embargo esto no es asi porque los datos
	  * pueden actualizarse, por lo que no se hace cache de "mias" ni "misfavoritas", ademas de dar un TTL de 1 minuto, la idea es evitar
	  * golpear a la BD cada segundo para la misma query (aunque para eso esta el query_cache) y renderizar todo de nuevo (el objetivo real a evitar aquí)
	*/
    
	$buffer = cache::obtener('qbe'.sha1($c));
	
	if ($buffer)
	{
	    echo $buffer;
	    echo '<!-- Cached stubs::CrearRejilla() -->';
	    return;
	}
	
	$r = db::consultar($c);
	
	$cc = 'SELECT COUNT(*) AS cantidad FROM fotos as tf LEFT JOIN cuentas USING(ID_cuenta) LEFT JOIN (SELECT ID_foto, COUNT(*) AS "cantidad_votos_a", AVG(rating) AS "rating_promedio_a" FROM votos GROUP BY ID_foto) AS tv USING(ID_foto) LEFT JOIN datos_pais USING(ID_pais) WHERE `cuentas`.`tipo` = "perra" '.$where;
	$cantidadFotos = mysql_fetch_assoc(db::consultar($cc));
	$cantidadFotos = $cantidadFotos['cantidad'];    

	ob_start();
	
	echo '<div id="fotos">';
	while ($r && $f = mysql_fetch_assoc($r))
	{
            $enlace = '<a class="conectar" href="'.PROY_URL.'conocer_chica_'.$f['ID_cuenta'].'_'.$f['ID_foto'].'_'.$f['usuario'].'.html">Contactar</a>';

	    if (isset($_GET['mias']))
		$enlace = '<input type="button" class="eliminar" rel="'.$f['ID_foto'].'" value="Eliminar" />';
	    
	    echo '
	    <a class="minicontenedor" href="'.PROY_URL.'chicas_lindas_'.$f['ID_cuenta'].'_'.$f['ID_foto'].'_'.$f['usuario'].'.html">
		<div class="imagen">
		<img src="'.PROY_URL.'crop_160_160_'.$f['foto_hash'].'.jpg" />
		</div>
		<table class="datos"><tr><td class="usuario">'.mb_substr(preg_replace('/[^\w]/','',$f['usuario']),0,15,'UTF-8').'</td><td class="pais">'.$f['pais'].'</td></tr></table>
		<table class="datos"><tr><td class="rating">Rating <span class="valorRating">'.$f['rating_promedio'].'/5.0</span></td><td class="votos">'.$f['cantidad_votos'].' votos</td></tr></table>
		<table class="datos"><tr><td class="tiempoPublicacion">'.stubs::timesince($f['creacion']).'</td><td class="vistas">'.$f['cantidad_vistas'].' vistas</td></tr></table>
		<table class="datos"><tr><td class="cantidadFotos">'.$f['cantidad_fotos'].' fotos</td><td class="contactar">'.$enlace.'</td></tr></table>	
	    </a>
	    ';
	}
	echo '</div>';
	
	if (!mysql_num_rows($r))
	{
	    echo '<p>No se encontraron resultados para su búsqueda</p>';
	    return;
	}
	
	if (!isset($ops['nopaginacion']))
	{
	    $cantidadDePaginas = ceil($cantidadFotos/40);
	    echo '<div id="paginador">';
	    echo '<a class="flecha" href="'.PROY_URL.'?p='.max($_GET['p']-1,0).'">&lt;&lt;</a>';
	    for($i=0;$i<$cantidadDePaginas;$i++)
		echo '<a '.($i == @$_GET['p'] ? 'class="psel"' : '').'href="'.PROY_URL.'?p='.$i.'">'.$i.'</a>';
	    echo '<a class="flecha" href="'.PROY_URL.'?p='.min($_GET['p']+1,$cantidadDePaginas).'">&gt;&gt;</a>';
	    echo '</div>';
	}
	
	$contenido = ob_get_clean();
	cache::guardar('qbe'.sha1($c), $contenido, '+1 minute');
	
	echo $contenido;
    }

    // http://www.php.net/manual/en/function.date.php#106097
    public static function timesince( $tsmp ) {
	$diffu = array(  'segundos'=>2, 'minutos' => 59, 'horas' => 3000, 'días' => 86400, 'meses' => 2678400,  'años' =>  63113851 );
	$diff = time() - strtotime($tsmp);
	$dt = 'justo ahora';
	foreach($diffu as $u => $n){
	    if($diff>$n) {
		$dt = 'hace '.floor($diff/$n).' '.$u;
	    }
	}
    
	return $dt;
    }

    // http://brenelz.com/blog/creating-an-ellipsis-in-php/
    public static function ellipsis($text, $max=100, $append='&hellip;')
    {
	if (strlen($text) <= $max) return $text;
	$out = substr($text,0,$max);
	if (strpos($text,' ') === FALSE) return $out.$append;
	return preg_replace('/\w+$/','',$out).$append;
    }

    
} // "stubs" class
?>
