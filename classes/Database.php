<?php
require_once 'config.php'; //Informações de conexão com o BD

class Database{
    private static $instance;

    public static function getInstance()
    {
        if(!isset(self::$instance)){
            try {
                self::$instance = new PDO('mysql:host=localhost;dbname=tmw_bd_servicedesk', 'root', ''); ///Necessário alterar as informações aqui também
                self::$instance->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            }
            catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
        return self::$instance;
    }

    public static function prepare($sql){
        return self::getInstance()->prepare($sql);
    }

    public static function lastInsertId(){ //Função pra pegar o último id inserido no banco
        return self::$instance->lastInsertId();
    }
} 