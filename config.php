<?
class general
{
    public static $config = array();
}

class web
{
    // http://www.webcheatsheet.com/PHP/get_current_page_url.php
    // Obtiene la URL actual, $stripArgs determina si eliminar la parte dinamica de la URL
    public static function URLactual($stripArgs=false,$friendly=false) {
        $pageURL = '';
        if (!$friendly)
        {
           $pageURL = 'http';
           if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
           $pageURL .= "://";
        }
        
        if ($_SERVER["SERVER_PORT"] != "80") {
           $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
           $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }
        
        if ($stripArgs) {$pageURL = preg_replace("/\?.*/", "",$pageURL);}
        
        if ($friendly)
        {
            $pageURL = preg_replace('/www\./', '',$pageURL);
            $pageURL = "www.$pageURL";
        }
        
        return $pageURL;
    }
}

/* Configuración de Google Analytics */
general::$config['google-UA'] = 'UA-27704071-1';
general::$config['titulo'] = 'Que Buena Estoy!';

/* Configuración de correos */    
general::$config['smtp_host'] = 'smtp.gmail.com';
general::$config['smtp_port'] = '465';
general::$config['smtp_correo'] = 'confirmacion@quebuenaestoy.com';
general::$config['smtp_nombre'] = 'Que Buena Estoy Confirmacion';
general::$config['smtp_usuario'] = 'confirmacion@quebuenaestoy.com';
general::$config['smtp_clave'] = 'Eyobayeyo123!';


/* Configuracion DB */
general::$config['db_usuario'] = 'quebuenaestoy';
general::$config['db_clave'] = 'quericaestoy';
general::$config['db_bd'] = 'quebuenaestoy';
general::$config['db_host'] = '127.0.0.1';

general::$config['URLactual'] = web::URLactual(true,false);

general::$config['paypal_production'] = false;

general::$config['paises'] = array('' => 'USA','mx' => 'México','cl' => 'Chile', 'co' => 'Colombia', 've' => 'Venezuela','sv' => 'El Salvador', 'hn' => 'Honduras', 'ni' => 'Nicaragua', 'gt' => 'Guatemala', 'cr' => 'Costa Rica', 'bz' => 'Belice', 'pa' => 'Panama');

define('PROY_URL',preg_replace(array("/\/?$/","/www./"),"","http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']))."/");
define('PROY_URL_AMIGABLE',"www.".preg_replace(array("/\/?$/","/www./"),"",$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']))."/");
define('PROY_URL_ACTUAL_DINAMICA',web::URLactual(false));
define('PROY_URL_ACTUAL',web::URLactual(true));
define('PROY_URL_ACTUAL_AMIGABLE',web::URLactual(true,true));
define('FACEBOX_APP_URL','http://apps.facebook.com/gay_match/');
?>
