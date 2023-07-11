<?php

class logs
{
    public static function registroAcciones($dni,$idServicio,$accion,$fecha)
    {
        $conStr = "mysql:host=localhost;dbname=la_comanda";
        $user ="yo";
        $pass ="cp35371754";
        $pdo = new PDO($conStr,$user,$pass);

        $sentencia = $pdo->prepare('INSERT INTO logs (dni,idServicio,accion,fecha)
                                   VALUES (:dni,:idServicio,:accion,:fecha)');
        $sentencia->bindValue(':dni', $dni);
        $sentencia->bindValue(':idServicio', $idServicio);
        $sentencia->bindValue(':accion', $accion);
        $sentencia->bindValue(':fecha', $fecha);


        if($sentencia->execute())
        {
            $pdo =null;
            return true;
        }
        else
        {
            $pdo =null;
            return false;
        }
    }
}

?>