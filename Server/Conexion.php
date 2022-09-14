<?php

class Conexion{
    private static $instancia;

    //Constructor
    private function __construct(){
        try{
            self::$instancia= new PDO('mysql:host=localhost;dbname=contactos', 'root', '2Cfgs');
        }catch (PDOException $e){
            self::$instancia=null;
        }
    }

    //Función para obtener la conexión
    public static function getConexion(){
        if(self::$instancia==null){
            new conexion();
        }
        return self::$instancia;
    }
}
