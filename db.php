<?php
// Funciones de base de datos
// Backend: Cassandra.
/*
 Funciones:
 + Abstraer las funciones de la base de datos.
 
 Limites:
 + No consultas especificas
 
 Datos:
 + Fecha de creación: 8 de Septiembre de 2011
 + Creado por: Vladimir Hidalgo
 + Diseño: Vladimir Hidalgo
 + Mantemiento:  Vladimir Hidalgo

*/

class db
{
    private static $link;
    public static $contador = 0;
    
    public static function conectar()
    {
        if (self::$link) return;
        
        self::$link = @mysql_connect(general::$config['db_host'], general::$config['db_usuario'], general::$config['db_clave']) or die ('DBCONERROR');
        @mysql_select_db(general::$config['db_bd'], self::$link) or die ('RRRBDD');
        mysql_query("set lc_time_names='es_SV'", self::$link);
        mysql_query("SET NAMES 'utf8'", self::$link);
    }

    public static function afectadas()
    {
        return mysql_affected_rows(self::$link);
    }
    
    public static function consultar($consulta)
    {
        self::$contador++;
        
        $r = mysql_query($consulta, self::$link);
        
        if (mysql_errno(self::$link))
        {
            error_log ($consulta);
            error_log (mysql_error(self::$link));
        }
        
        return $r;
    }
    
    public static function codex($dato)
    {
        return mysql_real_escape_string($dato, self::$link);
    }
    
    public static function reemplazar($tabla, $datos)
    {       
        $campos = $valores = NULL;
        foreach ($datos as $clave => $valor)
        {
            $arr_campos[]   = mysql_real_escape_string($clave, self::$link);
            $arr_valores[]  = mysql_real_escape_string($valor, self::$link);
        }
        $campos = implode (",", $arr_campos);
        $valores = "'".implode ("','", $arr_valores)."'";
        $c = "REPLACE INTO $tabla ($campos) VALUES ($valores)";
        $resultado = self::consultar($c);
        $id = mysql_insert_id (self::$link);
        return $id;        
    }
    
    public static function insertar($tabla, $datos, $datosSeguros = false)
    {       
        $campos = $valores = NULL;
        foreach ($datos as $clave => $valor)
        {
            if (!$datosSeguros)
            {
                $arr_campos[]   = mysql_real_escape_string($clave, self::$link);
                $arr_valores[]  = mysql_real_escape_string($valor, self::$link);
            } else
            {
                $arr_campos[]   = $clave;
                $arr_valores[]  = $valor;
            }
        }
        if (!$datosSeguros)
            $valores = "'".implode ("','", $arr_valores)."'";
        else
            $valores = implode (",", $arr_valores);
            
        $campos = implode (",", $arr_campos);
        
        $c = "INSERT INTO $tabla ($campos) VALUES ($valores)";
        $resultado = self::consultar($c);
        $id = mysql_insert_id (self::$link);
        return $id;        
    }
    
    public static function obtenerPorIndice($tabla, $llave, array $valores)
    {
        $valores = "'".implode("','",$valores)."'";
        $c = "SELECT * FROM `$tabla` WHERE `$llave` IN ($valores) LIMIT 1";
        $resultado = self::consultar($c);
        $f = mysql_fetch_assoc($resultado);
        return $f;
    }

    public static function obtenerCampoPorIndice($tabla, $llave, $campo, $valor)
    {
        $valor = "'".db::codex($valor)."'";
        $c = "SELECT * FROM `$tabla` WHERE `$llave` = $valor LIMIT 1";
        $resultado = self::consultar($c);
        $f = mysql_fetch_assoc($resultado);
        return $f[$campo];
    }
    
    public static function obtenerMultiplePorIndice($tabla, $llave, array $valores, $order, $limite)
    {
        if ($limite)
            $limite = "LIMIT $limite";
        
        if ($order)
            $order = "ORDER BY $order";
        
        $valores = "'".implode("','",$valores)."'";
        $c = "SELECT * FROM `$tabla` WHERE `$llave` IN ($valores) $order $limite";
        $resultado = self::consultar($c);
        return $resultado;
    }

    public static function obtenerPorFuzzy($tabla, $llave, $valores, $where, $limite)
    {
        
        $limite = $limite ? "LIMIT $limite" : '';
        $valores = "'".implode("','",$valores)."'";
        $c = "SELECT *, MATCH($llave) AGAINST ($valores WITH QUERY EXPANSION) AS puntaje FROM `$tabla` WHERE MATCH($llave) AGAINST ($valores WITH QUERY EXPANSION) $where ORDER BY puntaje DESC $limite";
        $resultado = self::consultar($c);
        return $resultado;
    }
    
    public static function verificarIndice($tabla, $llave, array $valores)
    {
        $valores = "'".implode("','",$valores)."'";
        $c = "SELECT COUNT(*) AS cantidad FROM `$tabla` WHERE `$llave` IN ($valores)";
        $resultado = self::consultar($c);
        $f = mysql_fetch_assoc($resultado);
        return $f['cantidad'];
    }
}
db::conectar();
?>