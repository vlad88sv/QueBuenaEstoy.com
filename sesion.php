<?php
session_start();

class sesion
{
    public static function info($campo)
    {
        if (isset($_SESSION['info'][$campo]))
            return $_SESSION['info'][$campo];
        else
            return null;
    }

    public static function cerrar()
    {
       unset($_SESSION);
       session_destroy ();
       return;
    }
    
    public static function iniciar($campo,$valor)
    {
        $_SESSION['autenticado'] = true;
        $_SESSION['info'] = db::obtenerPorIndice('cuentas',$campo,array($valor));
    }
    
    public static function iniciado()
    {
       return isset($_SESSION['autenticado']);
    }
    
    public static function obtenerCreditos()
    {
        $c = 'SELECT COALESCE(SUM(creditos),0) AS "cantidad_creditos" FROM credito WHERE ID_cuenta="'.self::info('ID_cuenta').'"';
        $r = db::consultar($c);
        $f = mysql_fetch_assoc($r);
        return $f['cantidad_creditos'];
    }
    
    public static function laTieneDesbloqueada($ID_cuenta)
    {
        $c = 'SELECT COUNT(*) AS "desbloqueado" FROM credito WHERE desbloqueo="'.$ID_cuenta.'" AND ID_cuenta="'.self::info('ID_cuenta').'"';
        $r = db::consultar($c);
        $f = mysql_fetch_assoc($r);
        return $f['desbloqueado'];
    }
    
    public static function obtenerBalance()
    {
        $c = 'SELECT FORMAT((COALESCE(SUM(creditos),0)*-0.5),2) AS "balance" FROM credito WHERE desbloqueo="'.self::info('ID_cuenta').'"';
        $r = db::consultar($c);
        $f = mysql_fetch_assoc($r);
        return '$'.$f['balance'];   
    }
    
    public static function obtenerMensajesNoLeidos()
    {
        $c = 'SELECT COALESCE(COUNT(*),0) AS cuenta FROM mensajes WHERE ID_destino='.self::info('ID_cuenta').' AND estado="nuevo"';
        $r = db::consultar($c);
        $f = mysql_fetch_assoc($r);
        return $f['cuenta'];
    }
}
?>
