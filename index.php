<?php
error_reporting(E_STRICT | E_ALL);
require_once('config.php');
require_once('db.php');
require_once('stubs.php');
require_once('ui.php');
require_once('sesion.php');
$HEAD_title = '';
ob_start();
if (!isset($_GET['accion']))
    $_GET['accion'] = 'fotos';

require_once('menu.php');

echo '<div id="contenido">';
$archivo = 'tpl/'.$_GET['accion'].'.tpl.php';
if (is_file($archivo))
    require_once($archivo); 
else
    echo '<p>ERROR "'.$_GET['accion'].'"</p>';
echo '</div>';

$body = ob_get_clean();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#" xml:lang="es" lang="es">
<head>
    <title>Mujeres bonitas de tu país - <?php echo $HEAD_title; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Style-type" content="text/css" />
    <meta http-equiv="Content-Script-type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="es" />
    <meta property="og:title" content="QueBuenaEstoy.com El lugar para las chicas reales mas buenas del mundo"/>
    <meta property="og:image" content="http://quebuenaestoy.com/img/logo_cuadrado.png"/>
    <meta property="og:description" content="Las chicas mas buenas de tu pais ahora en un solo lugar. Si eres chica puedes ganar mucho dinero subiendo tus fotos y conociendo chicos."/>
    <link rel='stylesheet' href='estilo.css' media='screen'>
    <link rel='stylesheet' href='css/facebox.css' media='screen'>
    <link rel='stylesheet' href='css/jquery.autocomplete.css' media='screen'>
    <link rel='stylesheet' href='css/jcarousel.css' media='screen'>
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.filestyle.mini.js"></script>
    <script type="text/javascript" src="js/jquery.cookie.js"></script>
    <script type="text/javascript" src="js/jquery.facebox.js"></script>
    <script type="text/javascript" src="js/jquery.scrollTo.js"></script>
    <script type="text/javascript" src="js/jquery.jcarousel.min.js"></script>
    <script type="text/javascript" src="js/fileuploader.js"></script>
    
    <script>
        $(function() {
            $("input[type=file]").filestyle({ 
                image: "img/boton_cargar.png",
                imageheight : 32,
                imagewidth : 32,
                width : 200
            });
            $('a.conectar').facebox();
            
            <?php if (!empty($_GET['fbox'])): ?>
            $.facebox({ajax: "<?php echo $_GET['fbox']; ?>"});
            <?php endif; ?>
        });
        
    </script>
    
    <script type="text/javascript">
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', '<?php echo general::$config['google-UA']; ?>']);
    _gaq.push(['_trackPageview']);
    
    (function() {
      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
    </script>
</head>
<body>
    <div id="fb-root"></div>
    <script>(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/es_LA/all.js#xfbml=1&appId=164036393700617";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>

    <div id="wrapper">
    <?php echo $body ?>
    </div> <!-- wrapper !-->
</body>
</html>