<?php

class Mesa
{
    public $idServicio;
    public $totalPagado;
    public $evaluacion;
    public $dniCliente;
    public $pedido;


    public static function guardarMesa($idServicio,$pedido,$totalPagado,$dniCliente,$fecha,$estado)
    {
        try
        {
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="yo";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);
            $pedidos = json_encode($pedido,true);


            $sentencia = $pdo->prepare('INSERT INTO mesa (idServicio,totalPagado,pedido,dniCliente,
                                                      estado,fecha) 
                                                    VALUES (:idServicio,:totalPagado,:pedido,:dniCliente,
                                                      :estado,:fecha)');
            $sentencia->bindValue(':idServicio', $idServicio);
            $sentencia->bindValue(':pedido', $pedidos);
            $sentencia->bindValue(':totalPagado', $totalPagado);
            $sentencia->bindValue(':dniCliente', $dniCliente);
            $sentencia->bindValue(':fecha', $fecha);
            $sentencia->bindValue(':estado', $estado);

            if($sentencia->execute())
            {
                return true;;
            }
            else
            {
                return false;
            }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            throw new Exception("usuario invalido");
        }
    }

    public static function guardarEvaluacion($idServicio,$evaluacion)
    {
        
        try
        {
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="yo";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);
            $e = json_encode($evaluacion,true);

            $sentencia = $pdo->prepare("UPDATE mesa SET evaluacion = :evaluacion
                                        WHERE idServicio = :idServicio");
            $sentencia->bindValue(':idServicio', $idServicio);
            $sentencia->bindValue(':evaluacion', $e);


            if($sentencia->execute())
            {
                return true;
            }
            else
            {
                return false;
            }

        }
        catch(PDOException $e)
        {
            $pdo = null;
            throw new Exception("usuario invalido");
        }
    }

    public static function traerVentasDesdeFecha($request, $response, $args)
    {
        try
        {
            $modo = token :: decodificarToken($request);
            if($modo == "socio")
            {
                $parametros = $request->getParsedBody();
                $fecha = validarFecha($parametros["fecha"]);
                $fecha30 = validarFecha(date('d-m-Y',strtotime($parametros["fecha"].'-30 day')));
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="yo";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
                $sentencia = $pdo->prepare("SELECT * FROM mesa");
                $total = 0;
                $totalAux = 0;
                $contador = 0;

                if($sentencia->execute())
                {
                    $resultado = $sentencia->fetchALL(PDO :: FETCH_ASSOC);
                    $pdo = null;

                    foreach($resultado as $m)
                    {
                        $fechaAux = validarFecha($m["fecha"]);
                        if($fechaAux<=$fecha && $fechaAux>=$fecha30)
                        {
                            $totalAux = $m["totalPagado"];
                            $total = $total+$totalAux;
                            $contador++;
                        }
                    }
                }
                
                if($contador>0)
                {
                    $payload = json_encode(array("mensaje"=>"total vendido $total\n"));
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                }
                else
                {
                    $payload = json_encode(array("mensaje"=>"no hay ventas en este periodo\n"));
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                }

            }
            else
            {
                $payload = json_encode(array("mensaje"=>"error de autenticacion\n"));
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
            }

        }
        catch(PDOException $e)
        {
            $pdo = null;
            throw new Exception("usuario invalido");
        }
    }
}

?>