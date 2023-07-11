<?php
use Firebase\JWT\JWT;
use Firebase\JWT\key;


class Control
{
    public static function testeoASD()
    {
        $conStr = "mysql:host=localhost;dbname=la_comanda";
        $user ="yo";
        $pass ="cp35371754";
        $pdo = new PDO($conStr,$user,$pass);

        $sentencia = $pdo->prepare("SELECT * FROM mesa");
        $sentencia->execute();
        $resultado = $sentencia->fetchALL(PDO :: FETCH_ASSOC);
        var_dump($resultado);


    }
    public static function obtenerDatosLogin($mail)
    {
        try
        {
           
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="yo";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare("SELECT * FROM staff WHERE mail = :mail");
            $sentencia->bindValue(':mail', $mail);
    
    
            if($sentencia->execute())
            {
                $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);

                if($resultado["estado"]=="activo")
                {
                    return $resultado;
                }
                else
                {
                    echo "usuario despedido";
                }
                
            }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }

    function crearUno($request, $response, $args)
    {
        $modo = token :: decodificarToken($request);

        if(!empty($modo))
        {
            try 
            {
                $control = 0;
                $parametros = $request->getParsedBody();
    
                if(($parametros["categoria"] == "mozo" || $parametros["categoria"] == "bartender" ||
                   $parametros["categoria"] == "cocinero" || $parametros["categoria"] == "cervecero") && $modo == "socio")
                {
                    $control++;
                    if(Empleado :: ExistenciaStaff($parametros["nombre"],$parametros["apellido"],$parametros["dni"],$parametros["mail"]))
                    {
                        $payload = json_encode(array("mensaje"=>"el miembro del staff que quiere crear se encuentra duplicado\n"));
                        $response->getBody()->write($payload);
                        $pdo = null;
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                    else
                    {
                        $conStr = "mysql:host=localhost;dbname=la_comanda";
                        $user ="yo";
                        $pass ="cp35371754";
                        $pdo = new PDO($conStr,$user,$pass);
        
                        $nombre= $parametros["nombre"];
                        $apellido = $parametros["apellido"];
                        $dni = $parametros["dni"];
                        $mail = $parametros["mail"];
                        $categoria = $parametros["categoria"];
                        $estado = $parametros["estado"];
                        $auxiliar = array();
                        $registro_presentismo = json_encode($auxiliar,true);
                        $registro_idServicios = json_encode($auxiliar,true);
                    
                        $sentencia = $pdo->prepare('INSERT INTO staff (nombre,apellido,dni,mail,categoria,estado,
                                                                       registro_presentismo,registro_idServicios) 
                                                    VALUES (:nombre,:apellido,:dni,:mail,:categoria,:estado,
                                                            :registro_presentismo,:registro_idServicios)');
                        $sentencia->bindValue(':nombre', $nombre);
                        $sentencia->bindValue(':apellido', $apellido);
                        $sentencia->bindValue(':dni', $dni);
                        $sentencia->bindValue(':mail', $mail);
                        $sentencia->bindValue(':categoria', $categoria);
                        $sentencia->bindValue(':estado', $estado);
                        $sentencia->bindValue(':registro_presentismo', $registro_presentismo);
                        $sentencia->bindValue(':registro_idServicios', $registro_idServicios);
        
        
                        if($sentencia->execute())
                        {
                            $payload = json_encode(array("mensaje"=>"miembro del staff: ".$nombre." ".$apellido." fue creado con exito\n"));
                            $response->getBody()->write($payload);
                            $pdo = null;
                            return $response->withHeader('Content-Type', 'application/json');
                        }
                        
                    }
                    
                   
                }
                
                if($parametros["categoria"] == "cliente" && ($modo == "socio" || $modo == "mozo"))
                {
                    $control++;
                    $estadoCliente =Orden :: ExistenciaCliente($parametros["nombre"],$parametros["apellido"],$parametros["dni"],$parametros["mail"]);
                    if($estadoCliente == "volvio" && $estadoCliente != "warning")
                    {
                        
                        $conStr = "mysql:host=localhost;dbname=la_comanda";
                        $user ="yo";
                        $pass ="cp35371754";
                        $pdo = new PDO($conStr,$user,$pass);
                        $nombre=$parametros["nombre"];
                        $apellido=$parametros["apellido"];
                        $dni = $parametros["dni"];
                        $registroUsuario = token :: insertarRegistro($request);
                        $accion = $request->getUri()->getPath();


                        
                        
                        $aux = (array)Control :: traeridServiciosCliente($parametros["dni"]);


                        $aux2 = controlID();
                        array_push($aux,$aux2);
                        $actualizacion = json_encode($aux);
                        
              
                        
                        $sentencia = $pdo->prepare("UPDATE ordenes SET idServicio = :idServicio, estado = :estado WHERE dni = :dni");
                        $sentencia->bindValue(':idServicio',$actualizacion);
                        $sentencia->bindValue(':estado',null);
                        $sentencia->bindValue(':dni', $dni);
    
                        if($sentencia->execute())
                        {
                            if(logs::registroAcciones($registroUsuario,$aux2,$accion,$parametros["fecha"]))
                            {
                                $payload = json_encode(array("mensaje"=>"cliente: ".$nombre." ".$apellido." ya existia, fue asignado una nueva clave con exito\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                            else
                            {
                                $payload = json_encode(array("mensaje"=>"error al guardar el registro de accion de empleado\ncliente: ".$nombre." ".$apellido." ya existia, fue asignado una nueva clave con exito\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }

                        
                        }
                    }
                    else
                    {
                        if($estadoCliente == "warning")
                        {
                            $nombre=$parametros["nombre"];
                            $apellido=$parametros["apellido"];
                            $payload = json_encode(array("mensaje"=>"cliente: ".$nombre." ".$apellido." no se creo ya que tiene una deuda pendiente\n"));
                            $response->getBody()->write($payload);
                            $pdo = null;
                            return $response->withHeader('Content-Type', 'application/json');
                        }
                        else
                        {
                        $conStr = "mysql:host=localhost;dbname=la_comanda";
                        $user ="admin";
                        $pass ="admin123";
                        $pdo = new PDO($conStr,$user,$pass);
                        $foto = $request->getUploadedFiles()['foto'];
                        $registroUsuario = token :: insertarRegistro($request);
                        $accion = $request->getUri()->getPath();

                        $nombre= $parametros["nombre"];
                        $apellido = $parametros["apellido"];
                        $dni = $parametros["dni"];
                        $mail = $parametros["mail"];
                        $categoria = $parametros["categoria"];
                        $fecha = $parametros["fecha"];
                        $auxiliar = array();    
                        $auxiliarNroId = array();
                        $id = controlID();//creo con la funcion
                        array_push($auxiliarNroId,$id);
                        $idServicio = json_encode($auxiliarNroId,true);
                        $pedido = json_encode($auxiliar,true);
                        $evaluacion = json_encode($auxiliar,true);
                        $ubicacionFoto = Orden :: moverImagen($foto,$dni,$id);
        
                        
                        $sentencia = $pdo->prepare('INSERT INTO ordenes (nombre,apellido,dni,mail,categoria,idServicio,fecha,foto) 
                                                    VALUES (:nombre,:apellido,:dni,:mail,:categoria,:idServicio,:fecha,:foto)');
                        $sentencia->bindValue(':nombre', $nombre);
                        $sentencia->bindValue(':apellido', $apellido);
                        $sentencia->bindValue(':mail', $mail);
                        $sentencia->bindValue(':dni', $dni);
                        $sentencia->bindValue(':categoria', $categoria);
                        $sentencia->bindValue(':idServicio', $idServicio);
                        $sentencia->bindValue(':fecha', $fecha);
                        $sentencia->bindValue(':foto', $ubicacionFoto);
                                                        
                        if($sentencia->execute())
                        {
                            if(logs::registroAcciones($registroUsuario,$id,$accion,$parametros["fecha"]))
                            {
                                $payload = json_encode(array("mensaje"=>"cliente: ".$nombre." ".$apellido." fue creado clave con exito\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                            else
                            {
                                $payload = json_encode(array("mensaje"=>"error al guardar el registro de accion de empleado\ncliente: ".$nombre." ".$apellido." ya existia, fue asignado una nueva clave con exito\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }

                        
                        }
                        }
                        
                    }
                    
                }
 
    
                if(($parametros["categoria"] == "bebida" || $parametros["categoria"] == "cerveza" ||
                   $parametros["categoria"] = "alimento") && $modo == "socio")
                {
                    $control = 1;

                    if(Producto :: existenciaProducto($parametros["articulo"],$parametros["precioVenta"],$parametros["categoria"]))
                    {
                            $payload = json_encode(array("mensaje"=>"El producto ya existe\n"));
                            $response->getBody()->write($payload);
                            return $response->withHeader('Content-Type', 'application/json');
                    }
                    else
                    {
                        $conStr = "mysql:host=localhost;dbname=la_comanda";
                        $user ="yo";
                        $pass ="cp35371754";
                        $pdo = new PDO($conStr,$user,$pass);
    
                        $articulo= $parametros["articulo"];
                        $categoria = $parametros["categoria"];
                        echo $categoria;
                        $precioVenta = $parametros["precioVenta"];
                        $idProducto = generarCodigoProducto();
    
                        $sentencia = $pdo->prepare('INSERT INTO productos (articulo,idProducto,categoria,precioVenta) 
                                                VALUES (:articulo,:idProducto,:categoria,:precioVenta)');
                        $sentencia->bindValue(':articulo', $articulo); 
                        $sentencia->bindValue(':categoria', $categoria); 
                        $sentencia->bindValue(':precioVenta', $precioVenta); 
                        $sentencia->bindValue(':idProducto', $idProducto); 
                        if($sentencia->execute())
                        {
                            $payload = json_encode(array("mensaje"=>"articulo ".$articulo." ".$idProducto." fue creado con exito\n"));
                            $response->getBody()->write($payload);
                            $pdo = null;
                            return $response->withHeader('Content-Type', 'application/json');
                        }
                    }
    
                }

                if($control == 0)
                {
                    $payload = json_encode(array("mensaje"=>"usuario $modo no valido\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');
                }
        
            }
            catch(PDOException $e)
            {
                $pdo = null;
                throw new Exception("usuario invalido");
            }
        }
        else
        {
            $payload = json_encode(array("mensaje"=>"usuario no valido\n"));
            $response->getBody()->write($payload);
            $pdo = null;
            return $response->withHeader('Content-Type', 'application/json');
        }
    
    }

    function traerColumnaCondicion($request, $response, $args, $ubicacion)
    {
        try 
        {
                $parametros = $request->getParsedBody();

                if($condicion == "ordenes")//busca por dni
                {
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="admin";
                    $pass ="admin123";
                    $pdo = new PDO($conStr,$user,$pass);

                    $sentencia = $pdo->prepare('SELECT dni FROM ordenes');
                    $sentencia->bindValue(':dni', $identificador);

                    if($sentencia->execute())
                    {
                        $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                        $pdo = null;
                        return $resultado;
    
                    }
                    $pdo = null;
                    
                }

                if($condicion == "empleado")//busca por dni
                {
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="admin";
                    $pass ="admin123";
                    $pdo = new PDO($conStr,$user,$pass);

                    $sentencia = $pdo->prepare('SELECT dni FROM staff');
                    $sentencia->bindValue(':dni', $identificador);

                    if($sentencia->execute())
                    {
                        $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                        $pdo = null;
                        return $resultado;
                    }
                    $pdo = null;
                }

                if($condicion == "producto")//busca por id Producto
                {
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="admin";
                    $pass ="admin123";
                    $pdo = new PDO($conStr,$user,$pass);

                    $sentencia = $pdo->prepare('SELECT idProducto FROM producto');
                    $sentencia->bindValue(':idProducto', $identificador);
                    if($sentencia->execute())
                    {
                        $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                        $pdo = null;
                        return $resultado;
                    }
                    $pdo = null;
                }

                if($condicion == "mesa")//busca por alfaNumerico
                {
                    //en proceso
                }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }

    public static function traerEmpleado($dni)
    {
        try
        {
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="yo";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare('SELECT * FROM staff WHERE dni = :dni');
            $sentencia->bindValue(':dni', $dni);
            if($sentencia->execute())
            {
                $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                return $resultado;
            }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }

    function traerUnoCondicion($request, $response, $args, $ubicacion,$identificador)
    {
        try 
        {
                $parametros = $request->getParsedBody();

                if($condicion == "ordenes")//busca por dni
                {
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="admin";
                    $pass ="admin123";
                    $pdo = new PDO($conStr,$user,$pass);

                    $sentencia = $pdo->prepare('SELECT * FROM ordenes WHERE dni = :dni');
                    $sentencia->bindValue(':dni', $identificador);

                    if($sentencia->execute())
                    {
                        $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                        $nombre = $resultado["nombre"];
                        $apellido = $resultado["apellido"];
                        $dni = $resultado["dni"];
                        $mail = $resultado["mail"];

        
                        if(!(is_null($nombre)))
                        {
                            $payload = json_encode(array("mensaje"=>"cliente: ".$nombre." ".$apellido." ".$dni." ".$mail." \n"));
                            $response->getBody()->write($payload);
                            $pdo = null;
                            return $response->withHeader('Content-Type', 'application/json');
                        }
                        else
                        {
                            $payload = json_encode(array("mensaje"=>"no se encontro al cliente\n"));
                            $response->getBody()->write($payload);
                            $pdo = null;
                            return $response->withHeader('Content-Type', 'application/json');
                        }
                    }
                }

                if($condicion == "staff")//busca por dni
                {
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="admin";
                    $pass ="admin123";
                    $pdo = new PDO($conStr,$user,$pass);

                    $sentencia = $pdo->prepare('SELECT * FROM staff WHERE dni = :dni');
                    $sentencia->bindValue(':dni', $identificador);

                    if($sentencia->execute())
                    {
                        $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                        $nombre = $resultado["nombre"];
                        $apellido = $resultado["apellido"];
                        $dni = $resultado["dni"];
                        $mail = $resultado["mail"];
                        $categoria = $resultado["categoria"];
                        $estado = $resultado["estado"];

                        if(!(is_null($nombre)))
                        {
                            $payload = json_encode(array("mensaje"=>"empleado: ".$nombre." ".$apellido." ".$dni." ".$mail." ".$categoria." ".$estado." \n"));
                            $response->getBody()->write($payload);
                            $pdo = null;
                            return $response->withHeader('Content-Type', 'application/json');
                        }
                        else
                        {
                            $payload = json_encode(array("mensaje"=>"no se encontro al empleado\n"));
                            $response->getBody()->write($payload);
                            $pdo = null;
                            return $response->withHeader('Content-Type', 'application/json');
                        }
                    }
                }

                if($condicion == "producto")//busca por id Producto
                {
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="admin";
                    $pass ="admin123";
                    $pdo = new PDO($conStr,$user,$pass);

                    $sentencia = $pdo->prepare('SELECT * FROM productos WHERE idProducto = :idProducto');
                    $sentencia->bindValue(':idProducto', $identificador);

                    if($sentencia->execute())
                    {
                        $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                        $articulo = $resultado["articulo"];
                        $idProducto = $resultado["idProducto"];
                        $categoria = $resultado["categoria"];
                        $precioVenta = $resultado["precioVenta"];
        
                        if(!(is_null($nombre)))
                        {
                            $payload = json_encode(array("mensaje"=>"producto: ".$articulo." ".$idProducto." ".$categoria." ".$precioVenta." \n"));
                            $response->getBody()->write($payload);
                            $pdo = null;
                            return $response->withHeader('Content-Type', 'application/json');
                        }
                        else
                        {
                            $payload = json_encode(array("mensaje"=>"no se encontro el producto\n"));
                            $response->getBody()->write($payload);
                            $pdo = null;
                            return $response->withHeader('Content-Type', 'application/json');
                        }
                    }
                }

                if($condicion == "mesa")//busca por alfaNumerico
                {
                    //en proceso
                }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    
    }

   

    public static function traerListaPedidos()//para saber pedidos q tengo q preparar como cocinero/bartender
    {                                                                    
        try 
        {

            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="yo";
            $pass ="cp35371754";
            $condicion = "esperando";
            $pdo = new PDO($conStr,$user,$pass);
            $sentencia = $pdo->prepare('SELECT pedido FROM ordenes where estado = :condicion');
            $sentencia->bindValue(':condicion', $condicion);

    
            if($sentencia->execute())
            {
                $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                $pdo = null;
                return $resultado;
            }
            
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }


   

    public static function traerTodo($condicion)
    {
        
        try
        {
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="yo";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);
            
            

            if($condicion == "ordenes")
            {
                
                $sentencia = $pdo->prepare('SELECT * FROM ordenes');

                if($sentencia->execute())
                {
                    $resultado = $sentencia->fetchALL(PDO :: FETCH_ASSOC);
                    return $resultado;

                }
            }

            if($condicion == "staff")
            {
                $sentencia = $pdo->prepare('SELECT * FROM staff');

                if($sentencia->execute())
                {
                    $resultado = $sentencia->fetchALL(PDO :: FETCH_ASSOC);
                    
                    return $resultado;

                }
                
            }

            if($condicion == "productos")
            {
                $sentencia = $pdo->prepare('SELECT * FROM productos');

                if($sentencia->execute())
                {
                    $resultado = $sentencia->fetchALL(PDO :: FETCH_ASSOC);

                    
                    return $resultado;

                }
            }


            if($condicion == "mesa")
            {
                //en construccion
            }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }

    public static function listarCategoria($request, $response, $args)
    {
        try
        {
            $lista = $args['lista'];
            $modo = token :: decodificarToken($request);
            $control = 0;
            echo $lista."--".$modo;
            if($lista == 'staff' && $modo == 'socio')
            {

                $control = 1;
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="yo";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
                $sentencia = $pdo->prepare('SELECT * FROM staff');

                if($sentencia->execute())
                {
                    $resultado = $sentencia->fetchALL(PDO :: FETCH_ASSOC);

                    foreach($resultado as $s)
                    {
                        echo $s["nombre"]."--".$s["apellido"]."--".$s["dni"]."--".$s["mail"]."--".$s["categoria"]."--".$s["estado"]."\n";
                    }
                    $pdo = null;
                    
                    $payload = json_encode(array("mensaje"=>"exito\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');

                }
            }


            if($lista == "productos" && ($modo =="socio" || $modo ="mozo"))
            {
                $control = 1;
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="yo";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
                $sentencia = $pdo->prepare('SELECT * FROM productos');

                if($sentencia->execute())
                {
                    $resultado = $sentencia->fetchALL(PDO :: FETCH_ASSOC);

                    foreach($resultado as $s)
                    {
                        echo $s["articulo"]."--".$s["idProducto"]."--".$s["categoria"]."--".$s["precioVenta"]."\n";
                    }
                    $pdo = null;
                    
                    $payload = json_encode(array("mensaje"=>"exito\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');

                }
            }

            if($lista == 'ordenes' && $modo =='socio' || $modo ='mozo')
            {
                $control = 1;
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="yo";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
                $sentencia = $pdo->prepare('SELECT * FROM ordenes');

                if($sentencia->execute())
                {
                    $resultado = $sentencia->fetchALL(PDO :: FETCH_ASSOC);

                    foreach($resultado as $s)
                    {
                        echo $s["nombre"]."--".$s["apellido"]."--".$s["dni"]."--".$s["mail"]."\n";
                    }
                    $pdo = null;
                    
                    $payload = json_encode(array("mensaje"=>"exito\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');

                }
            }


            if($control == 0)
            {
                $payload = json_encode(array("mensaje"=>"error de solicitud\n"));
                $response->getBody()->write($payload);
                $pdo = null;
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }

    public static function retornarClaveAlfa($DNI)
    {
        try
        {
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="yo";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);
            $sentencia = $pdo->prepare("SELECT * FROM ordenes WHERE dni = :dni");
            $sentencia->bindValue(':dni', $DNI);
            if($sentencia->execute())
            {
                $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                return $resultado;
            }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }

    public static function retornarClientePorIDServicio($idServico)
    {
        try
        {
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="yo";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);
            $sentencia = $pdo->prepare("SELECT * FROM ordenes");

            if($sentencia->execute())
            {
                $resultado = $sentencia->fetchALL(PDO :: FETCH_ASSOC);
                foreach($resultado as $o)
                {
                    $aux = json_decode($o["idServicio"],true);
                    for($i=0;$i<count($aux);$i++)
                    {
                        if($aux[$i] == $idServico)
                        {
                            $retorno = $o["dni"];
                            break;
                        }
                    }
                }
            }

            if($retorno > 0)
            {
                return $retorno;
            }
            else
            {
                return false;
            }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }

    function realizarPedido($request, $response, $args)
    {
        try
        {
            $modo = token :: decodificarToken($request);
            $registroUsuario = token :: insertarRegistro($request);
            $accion = $request->getUri()->getPath();

            if($modo == "mozo" || $modo =="socio")
            {
                $parametros = $request->getParsedBody();
                $DNI = $parametros["dni"];
                $idProducto = $parametros["idProducto"];
                $cantidad = $parametros["cantidad"];
                $fecha = $parametros["fecha"];
                

            $cliente = Control :: retornarClaveAlfa($DNI);//me devuelve el cliente

            
            $aux= $cliente["idServicio"];

            $listaOrdenes = (array)json_decode($aux,true);
            $index = count($listaOrdenes);
            if($index > 1)
            {
                $orden = $listaOrdenes[$index-1];
            }
            else
            {
                $orden = $listaOrdenes[0];
            }

            
        

            $pedido = array("idProducto"=>$idProducto,
                            "cantidad"=>$cantidad,
                            "idServicio"=>$orden,
                            "fecha"=>$fecha,
                            "estado"=>"esperando");
            
            $auxP= $cliente["pedido"];
        
            $listaPedido = (array)json_decode($auxP,true);
            array_push($listaPedido,$pedido);
            
            $modificacionP = json_encode($listaPedido,true);
            $estado = "esperando";
            
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="yo";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);
            $sentencia = $pdo->prepare("UPDATE ordenes SET pedido = :pedido, estado =:estado  WHERE dni = :dni");
            $sentencia->bindValue(':pedido',$modificacionP);
            $sentencia->bindValue(':estado',$estado);
            $sentencia->bindValue(':dni', $parametros["dni"]);

            if($sentencia->execute())
            {
                if(logs::registroAcciones($registroUsuario,$orden,$accion,$fecha))
                {
                    $payload = json_encode(array("mensaje"=>"exito al realizar el pedido\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }
            }
            else
            {
                $payload = json_encode(array("mensaje"=>"error de autenticacion\n"));
                $response->getBody()->write($payload);
                $pdo = null;
                return $response->withHeader('Content-Type', 'application/json');
            }
            
            
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }

    }

    public static function listoParaServir($request, $response, $args)
    {
        try
        {
            $modo = token :: decodificarToken($request);
            $control = 0;
            $registroUsuario = token :: insertarRegistro($request);
            $accion = $request->getUri()->getPath();
            $parametros = $request->getParsedBody();
            $idProducto = $parametros["idProducto"];
            $idServicio = $parametros["idServicio"];
            $cantidad = $parametros["cantidad"];
            $dni = control :: retornarClientePorIDServicio($idServicio);

            if($modo == "socio")
            {
                $control = 1;
                $listado =  Control :: traerListaPedidos();
                $contador = 0;
                $verificador = 0;
            

                for($i = 0; $i<count($listado);$i++)
                {
                    foreach($listado[$i] as $p)
                    {
                        $aux = json_decode($p,true);
   
                        foreach($aux as $orden)
                        {
                            $cat = Producto :: retornarCategoria($orden["idProducto"]);//crear esto
                            
                            if($orden["estado"] == "esperando" && $orden["cantidad"]==$cantidad && 
                               $orden["idProducto"] == $idProducto && $orden["idServicio"] == $idServicio)
                            {
                                $verificador++;
                                $orden["estado"] = "preparado";
                                echo "servicio ".$orden["idServicio"]." id producto ".$orden["idProducto"]." cantidad ".$orden["cantidad"]." estado ".$orden["estado"]." categoria ".$cat."\n";
                                break;
                            }
                            $contador++;
                        }

                        if($verificador>0)
                        {
                            
                            $listaModificada = json_decode($listado[$i]["pedido"],true);
                            $listaModificada[$contador]["estado"]="preparado";
                            $listaFinal = $listaModificada;
                            $listado[$i]=$listaFinal;
                            $listaUpdate = json_encode($listado[$i],true);
                            break;
                        }
                        
                    }
                }

                if($verificador>0)
                {

                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="yo";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
                    $sentencia = $pdo->prepare("UPDATE ordenes SET pedido = :pedido WHERE dni = :dni");
                    $sentencia->bindValue(':pedido',$listaUpdate);
                    $sentencia->bindValue(':dni', $dni);

                    if($sentencia->execute())
                    {
                            if(logs::registroAcciones($registroUsuario,$idServicio,$accion,$parametros["fecha"]))
                            {
                                $payload = json_encode(array("mensaje"=>"pedido listo para servir\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                            else
                            {
                                $payload = json_encode(array("mensaje"=>"error al guardar el registro de accion de empleado\ncliente: ".$nombre." ".$apellido." ya existia, fue asignado una nueva clave con exito\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                    }

                }
                else
                {
                    $payload = json_encode(array("mensaje"=>"no se encontro un pedido con esos datos\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }

            if($modo == "bartender")
            {
                $control = 1;
                $listado =  Control :: traerListaPedidos();
                $contador = 0;
                $verificador = 0;
            

                for($i = 0; $i<count($listado);$i++)
                {
                    foreach($listado[$i] as $p)
                    {
                        $aux = json_decode($p,true);
   
                        foreach($aux as $orden)
                        {
                            $cat = Producto :: retornarCategoria($orden["idProducto"]);
                            
                            if($orden["estado"] == "esperando" && $orden["cantidad"]==$cantidad && 
                               $orden["idProducto"] == $idProducto && $orden["idServicio"] == $idServicio && $cat == "bebida")
                            {
                                $verificador++;
                                $orden["estado"] = "preparado";
                                echo "servicio ".$orden["idServicio"]." id producto ".$orden["idProducto"]." cantidad ".$orden["cantidad"]." estado ".$orden["estado"]." categoria ".$cat."\n";
                                break;
                            }
                            $contador++;
                        }

                        if($verificador>0)
                        {
                            
                            $listaModificada = json_decode($listado[$i]["pedido"],true);
                            $listaModificada[$contador]["estado"]="preparado";
                            $listaFinal = $listaModificada;
                            $listado[$i]=$listaFinal;
                            $listaUpdate = json_encode($listado[$i],true);
                            break;
                        }
                        
                    }
                }

                if($verificador>0)
                {

                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="yo";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
                    $sentencia = $pdo->prepare("UPDATE ordenes SET pedido = :pedido WHERE dni = :dni");
                    $sentencia->bindValue(':pedido',$listaUpdate);
                    $sentencia->bindValue(':dni', $dni);

                    if($sentencia->execute())
                    {
                            if(logs::registroAcciones($registroUsuario,$idServicio,$accion,$parametros["fecha"]))
                            {
                                $payload = json_encode(array("mensaje"=>"pedido listo para servir\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                            else
                            {
                                $payload = json_encode(array("mensaje"=>"error al guardar el registro de accion de empleado\ncliente: ".$nombre." ".$apellido." ya existia, fue asignado una nueva clave con exito\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                    }

                }
                else
                {
                    $payload = json_encode(array("mensaje"=>"no se encontro un pedido con esos datos\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }

            if($modo == "cocinero")
            {
                $control = 1;
                $listado =  Control :: traerListaPedidos();
                $contador = 0;
                $verificador = 0;
            

                for($i = 0; $i<count($listado);$i++)
                {
                    foreach($listado[$i] as $p)
                    {
                        $aux = json_decode($p,true);
   
                        foreach($aux as $orden)
                        {
                            $cat = Producto :: retornarCategoria($orden["idProducto"]);
                            
                            if($orden["estado"] == "esperando" && $orden["cantidad"]==$cantidad && 
                               $orden["idProducto"] == $idProducto && $orden["idServicio"] == $idServicio && $cat == "alimento")
                            {
                                $verificador++;
                                $orden["estado"] = "preparado";
                                echo "servicio ".$orden["idServicio"]." id producto ".$orden["idProducto"]." cantidad ".$orden["cantidad"]." estado ".$orden["estado"]." categoria ".$cat."\n";
                                break;
                            }
                            $contador++;
                        }

                        if($verificador>0)
                        {
                            
                            $listaModificada = json_decode($listado[$i]["pedido"],true);
                            $listaModificada[$contador]["estado"]="preparado";
                            $listaFinal = $listaModificada;
                            $listado[$i]=$listaFinal;
                            $listaUpdate = json_encode($listado[$i],true);
                            break;
                        }
                        
                    }
                }

                if($verificador>0)
                {

                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="yo";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
                    $sentencia = $pdo->prepare("UPDATE ordenes SET pedido = :pedido WHERE dni = :dni");
                    $sentencia->bindValue(':pedido',$listaUpdate);
                    $sentencia->bindValue(':dni', $dni);

                    if($sentencia->execute())
                    {
                            if(logs::registroAcciones($registroUsuario,$idServicio,$accion,$parametros["fecha"]))
                            {
                                $payload = json_encode(array("mensaje"=>"pedido listo para servir\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                            else
                            {
                                $payload = json_encode(array("mensaje"=>"error al guardar el registro de accion de empleado\ncliente: ".$nombre." ".$apellido." ya existia, fue asignado una nueva clave con exito\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                    }

                }
                else
                {
                    $payload = json_encode(array("mensaje"=>"no se encontro un pedido con esos datos\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }

            if($modo == "cervecero")
            {
                $control = 1;
                $listado =  Control :: traerListaPedidos();
                $contador = 0;
                $verificador = 0;
            

                for($i = 0; $i<count($listado);$i++)
                {
                    foreach($listado[$i] as $p)
                    {
                        $aux = json_decode($p,true);
   
                        foreach($aux as $orden)
                        {
                            $cat = Producto :: retornarCategoria($orden["idProducto"]);
                            
                            if($orden["estado"] == "esperando" && $orden["cantidad"]==$cantidad && 
                               $orden["idProducto"] == $idProducto && $orden["idServicio"] == $idServicio && $cat == "cerveza")
                            {
                                $verificador++;
                                $orden["estado"] = "preparado";
                                echo "servicio ".$orden["idServicio"]." id producto ".$orden["idProducto"]." cantidad ".$orden["cantidad"]." estado ".$orden["estado"]." categoria ".$cat."\n";
                                break;
                            }
                            $contador++;
                        }

                        if($verificador>0)
                        {
                            
                            $listaModificada = json_decode($listado[$i]["pedido"],true);
                            $listaModificada[$contador]["estado"]="preparado";
                            $listaFinal = $listaModificada;
                            $listado[$i]=$listaFinal;
                            $listaUpdate = json_encode($listado[$i],true);
                            break;
                        }
                        
                    }
                }

                if($verificador>0)
                {

                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="yo";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
                    $sentencia = $pdo->prepare("UPDATE ordenes SET pedido = :pedido WHERE dni = :dni");
                    $sentencia->bindValue(':pedido',$listaUpdate);
                    $sentencia->bindValue(':dni', $dni);

                    if($sentencia->execute())
                    {
                            if(logs::registroAcciones($registroUsuario,$idServicio,$accion,$parametros["fecha"]))
                            {
                                $payload = json_encode(array("mensaje"=>"pedido listo para servir\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                            else
                            {
                                $payload = json_encode(array("mensaje"=>"error al guardar el registro de accion de empleado\ncliente: ".$nombre." ".$apellido." ya existia, fue asignado una nueva clave con exito\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                    }

                }
                else
                {
                    $payload = json_encode(array("mensaje"=>"no se encontro un pedido con esos datos\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }

        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }

    public static function mostrarPedidosPendientes($request, $response, $args)//en categoria le envio
    {                                                                        //si cerveza, bebida, etc
        try
        {//tengo q traer todos los servicios q existen
            $modo = token :: decodificarToken($request);
            $control = 0;

            if($modo == "socio")
            {
                $control = 1;
                $listado =  Control :: traerListaPedidos();
                $contador = 0;
            

                for($i = 0; $i<count($listado);$i++)
                {
                    foreach($listado[$i] as $p)
                    {
                        $aux = json_decode($p,true);
   
                        foreach($aux as $orden)
                        {
                            $cat = Producto :: retornarCategoria($orden["idProducto"]);//crear esto
                            
                            
                            if($orden["estado"] == "esperando")
                            {
                            echo "servicio ".$orden["idServicio"]." id producto ".$orden["idProducto"]." cantidad ".$orden["cantidad"]." estado ".$orden["estado"]." categoria ".$cat."\n";
                            $contador++;
                            }
                        }
                        
                    }
                }

                if($contador>0)
                {
                    $payload = json_encode(array("mensaje"=>"cantidad de pedidos pendientes $contador\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');
                }
                else
                {
                    $payload = json_encode(array("mensaje"=>"no hay pedidos pendientes\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');
                }

            }
            
            if($modo == "bartender")
            {
                $control = 1;
                $listado =  Control :: traerListaPedidos();
                $contador = 0;
            

                for($i = 0; $i<count($listado);$i++)
                {
                    foreach($listado[$i] as $p)
                    {
                        $aux = json_decode($p,true);
   
                        foreach($aux as $orden)
                        {
                            $cat = Producto :: retornarCategoria($orden["idProducto"]);//crear esto
                            
                            
                            if($orden["estado"] == "esperando" && $cat == "bebida")
                            {
                            echo " servicio ".$orden["idServicio"]." id producto ".$orden["idProducto"]." cantidad ".$orden["cantidad"]." estado ".$orden["estado"]." categoria ".$cat."\n";
                            $contador++;
                            }
                        }
                        
                    }
                }

                if($contador>0)
                {
                    $payload = json_encode(array("mensaje"=>"cantidad de pedidos pendientes $contador\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');
                }
                else
                {
                    $payload = json_encode(array("mensaje"=>"no hay pedidos pendientes\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }

            if($modo == "cervecero")
            {            
                $control = 1;
                $listado =  Control :: traerListaPedidos();
                $contador = 0;
            

                for($i = 0; $i<count($listado);$i++)
                {
                    foreach($listado[$i] as $p)
                    {
                        $aux = json_decode($p,true);
   
                        foreach($aux as $orden)
                        {
                            $cat = Producto :: retornarCategoria($orden["idProducto"]);//crear esto
                            
                            
                            if($orden["estado"] == "esperando" && $cat == "cerveza")
                            {
                            echo "servicio ".$orden["idServicio"]." id producto ".$orden["idProducto"]." cantidad ".$orden["cantidad"]." estado ".$orden["estado"]." categoria ".$cat."\n";
                            $contador++;
                            }
                        }
                        
                    }
                }

                if($contador>0)
                {
                    $payload = json_encode(array("mensaje"=>"cantidad de pedidos pendientes $contador\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');
                }
                else
                {
                    $payload = json_encode(array("mensaje"=>"no hay pedidos pendientes\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }

            if($modo == "cocinero")
            {
                $control = 1;
                $listado =  Control :: traerListaPedidos();
                $contador = 0;
            

                for($i = 0; $i<count($listado);$i++)
                {
                    foreach($listado[$i] as $p)
                    {
                        $aux = json_decode($p,true);
   
                        foreach($aux as $orden)
                        {
                            $cat = Producto :: retornarCategoria($orden["idProducto"]);//crear esto
                            
                            
                            if($orden["estado"] == "esperando" && $cat == "alimento")
                            {
                            echo "servicio ".$orden["idServicio"]." id producto ".$orden["idProducto"]." cantidad ".$orden["cantidad"]." estado ".$orden["estado"]." categoria ".$cat."\n";
                            $contador++;
                            }
                        }
                        
                    }
                }

                if($contador>0)
                {
                    $payload = json_encode(array("mensaje"=>"cantidad de pedidos pendientes $contador\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');
                }
                else
                {
                    $payload = json_encode(array("mensaje"=>"no hay pedidos pendientes\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }

            if($control == 0)
            {
                $payload = json_encode(array("mensaje"=>"error de autenticación\n"));
                $response->getBody()->write($payload);
                $pdo = null;
                return $response->withHeader('Content-Type', 'application/json');
            }

        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }
    public static function entregarPlato($request, $response, $args)
    {
        try
        {
            $modo = token :: decodificarToken($request);

            if($modo == "socio" || $modo  == "mozo")
            {
                $registroUsuario = token :: insertarRegistro($request);
                $accion = $request->getUri()->getPath();
                $parametros = $request->getParsedBody();
                $idProducto = $parametros["idProducto"];
                $idServicio = $parametros["idServicio"];
                $cantidad = $parametros["cantidad"];
                $dni = control :: retornarClientePorIDServicio($idServicio);
                $listado =  Control :: traerListaPedidos();
                $contador = 0;
                $verificador = 0;
            

                for($i = 0; $i<count($listado);$i++)
                {
                    foreach($listado[$i] as $p)
                    {
                        $aux = json_decode($p,true);
   
                        foreach($aux as $orden)
                        {
                            $cat = Producto :: retornarCategoria($orden["idProducto"]);//crear esto
                            
                            if($orden["estado"] == "preparado" && $orden["cantidad"]==$cantidad && 
                               $orden["idProducto"] == $idProducto && $orden["idServicio"] == $idServicio)
                            {
                                $verificador++;
                                $orden["estado"] = "servido";
                                echo "servicio ".$orden["idServicio"]." id producto ".$orden["idProducto"]." cantidad ".$orden["cantidad"]." estado ".$orden["estado"]." categoria ".$cat."\n";
                                break;
                            }
                            $contador++;
                        }

                        if($verificador>0)
                        {
                            
                            $listaModificada = json_decode($listado[$i]["pedido"],true);
                            $listaModificada[$contador]["estado"]="servido";
                            $listaFinal = $listaModificada;
                            $listado[$i]=$listaFinal;
                            $listaUpdate = json_encode($listado[$i],true);
                            break;
                        }
                        
                    }
                }

                if($verificador>0)
                {
                    
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="yo";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
                    $sentencia = $pdo->prepare("UPDATE ordenes SET pedido = :pedido WHERE dni = :dni");
                    $sentencia->bindValue(':pedido',$listaUpdate);
                    $sentencia->bindValue(':dni', $dni);

                    if($sentencia->execute())
                    {
                            if(logs::registroAcciones($registroUsuario,$idServicio,$accion,$parametros["fecha"]))
                            {
                                $payload = json_encode(array("mensaje"=>"pedido servido\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                            else
                            {
                                $payload = json_encode(array("mensaje"=>"error al guardar el registro de accion de empleado\ncliente: ".$nombre." ".$apellido." ya existia, fue asignado una nueva clave con exito\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                    }

                }
                else
                {
                    $payload = json_encode(array("mensaje"=>"no se encontro un pedido con esos datos\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }
            else
            {
                
                $payload = json_encode(array("mensaje"=>"Error de autenticacion\n"));
                $response->getBody()->write($payload);
                $pdo = null;
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }
    public static function mostrarPedidosPreparados($request, $response, $args)
    {
        $modo = token :: decodificarToken($request);
        $control = 0;

        if($modo == "socio" || $modo == "mozo")
        {
                $control = 1;
                $listado =  Control :: traerListaPedidos();
                $contador = 0;

                for($i = 0; $i<count($listado);$i++)
                {
                    foreach($listado[$i] as $p)
                    {
                        $aux = json_decode($p,true);
   
                        foreach($aux as $orden)
                        {
                            $dni = control :: retornarClientePorIDServicio($orden["idServicio"]);
                            $cat = Producto :: retornarCategoria($orden["idProducto"]);//crear esto
                            
                            if($orden["estado"] == "preparado")
                            {
                                $contador++;
                                echo "cliente ".$dni." servicio ".$orden["idServicio"]." id producto ".$orden["idProducto"]." cantidad ".$orden["cantidad"]." estado ".$orden["estado"]." categoria ".$cat."\n";
                            }
                            
                        }
                    }
        
                }
                $payload = json_encode(array("mensaje"=>"listado de pedidos para entregar $contador\n"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
        }
        else
        {
            $payload = json_encode(array("mensaje"=>"Error de autenticacion"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public static function clienteComiendo($request, $response, $args)
    {
        try
        {
            $modo = token :: decodificarToken($request);
            $registroUsuario = token :: insertarRegistro($request);
            $accion = $request->getUri()->getPath();
            $parametros = $request->getParsedBody();
            $dni = $parametros["dni"];
            $idServicio = $parametros["idServicio"];
            $fecha = $parametros["fecha"];
            
    
            if($modo == "socio" || $modo == "mozo")
            {
                $est = Control :: estadoCliente($dni);
                $estadoCliente = $est["estado"];
                if($estadoCliente == "esperando")
                {
                    $listado = Control :: verificarPedidosCliente($dni);
                    $contador = 0;
                    $orden = json_decode($listado["pedido"],true);

                    for($i=0;$i<count($orden);$i++)
                    {
                        if($orden[$i]["estado"] != "servido")
                        {
                            $contador++;    
                        }
                    }
                    
                    if($contador == 0)
                    {
                        $conStr = "mysql:host=localhost;dbname=la_comanda";
                        $user ="yo";
                        $pass ="cp35371754";
                        $estado = "comiendo";
                        $pdo = new PDO($conStr,$user,$pass);
                        $sentencia = $pdo->prepare("UPDATE ordenes SET estado = :estado WHERE dni = :dni");
                        $sentencia->bindValue(':dni', $dni);
                        $sentencia->bindValue(':estado',$estado);

                        if($sentencia->execute())
                        {
                            if(logs::registroAcciones($registroUsuario,$idServicio,$accion,$fecha))
                            {
                                $payload = json_encode(array("mensaje"=>"cliente $dni comiendo\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                            else
                            {
                                $payload = json_encode(array("mensaje"=>"error al guardar el registro de accion de empleado\ncliente: ".$nombre." ".$apellido." ya existia, fue asignado una nueva clave con exito\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                        }
                    }
                    else
                    {
                        $payload = json_encode(array("mensaje"=>" el cliente tiene pedidos pendientes\n"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                }
                else
                {
                    $payload = json_encode(array("mensaje"=>" error al cambiar el estado del cliente\n"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                }
                    
            }
            else
            {
                $payload = json_encode(array("mensaje"=>"Error de autenticacion"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
       
    }

    public static function clientePagando($request, $response, $args)
    {
        try
        {
            $modo = token :: decodificarToken($request);
            $registroUsuario = token :: insertarRegistro($request);
            $accion = $request->getUri()->getPath();
            $parametros = $request->getParsedBody();
            $dni = $parametros["dni"];
            $idServicio = $parametros["idServicio"];
            $fecha = $parametros["fecha"];
            
    
            if($modo == "socio" || $modo == "mozo")
            {
                $est = Control :: estadoCliente($dni);
                $estadoCliente = $est["estado"];
                if($estadoCliente == "comiendo")
                {
                    $listado = Control :: verificarPedidosCliente($dni);
                    $contador = 0;
                    $orden = json_decode($listado["pedido"],true);
                    $totalAux = 0;
                    $total = 0;

                    for($i=0;$i<count($orden);$i++)
                    {
                        if($orden[$i]["estado"] == "servido")
                        {

                            $totalAux = $orden[$i]["cantidad"] * Producto :: calcularPrecio($orden[$i]["idProducto"]);//calcular costo
                            $total = $total + $totalAux;
                           
                        }
                        else
                        {
                            $contador++;    
                        }
                    }

                    
                    if($contador  == 0)
                    {
                        $conStr = "mysql:host=localhost;dbname=la_comanda";
                        $user ="yo";
                        $pass ="cp35371754";
                        $estado = "pagando";
                        $pdo = new PDO($conStr,$user,$pass);
                        $sentencia = $pdo->prepare("UPDATE ordenes SET estado = :estado, total = :total WHERE dni = :dni");
                        $sentencia->bindValue(':dni', $dni);
                        $sentencia->bindValue(':estado',$estado);
                        $sentencia->bindValue(':total',$total);

                        if($sentencia->execute())
                        {
                            if(logs::registroAcciones($registroUsuario,$idServicio,$accion,$fecha))
                            {
                                $payload = json_encode(array("mensaje"=>"cliente $dni pagando un total de $total\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                            else
                            {
                                $payload = json_encode(array("mensaje"=>"error al guardar el registro de accion de empleado\ncliente: ".$nombre." ".$apellido." ya existia, fue asignado una nueva clave con exito\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                        }
                    }
                    else
                    {
                        $payload = json_encode(array("mensaje"=>" el cliente tiene pedidos pendientes\n"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                }
                else
                {
                    $payload = json_encode(array("mensaje"=>" error al cambiar el estado del cliente\n"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                }
                    
            }
            else
            {
                $payload = json_encode(array("mensaje"=>"Error de autenticacion"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
       
    }

    public static function cerrarComanda($request, $response, $args)
    {
        try
        {
            $modo = token :: decodificarToken($request);
            $registroUsuario = token :: insertarRegistro($request);
            $accion = $request->getUri()->getPath();
            $parametros = $request->getParsedBody();
            $dni = $parametros["dni"];
            $idServicio = $parametros["idServicio"];
            $fecha = $parametros["fecha"];
            
    
            if($modo == "socio")
            {
                $est = Control :: estadoCliente($dni);
                $estadoCliente = $est["estado"];
                if($estadoCliente == "pagando")
                {
                    $listado = Control :: verificarPedidosCliente($dni);
                    $contador = 0;
                    $orden = json_decode($listado["pedido"],true);
                    $totalAux = 0;
                    $total = 0;

                    for($i=0;$i<count($orden);$i++)
                    {
                        if($orden[$i]["estado"] == "servido")
                        {

                            $totalAux = $orden[$i]["cantidad"] * Producto :: calcularPrecio($orden[$i]["idProducto"]);//calcular costo
                            $total = $total + $totalAux;
                           
                        }
                        else
                        {
                            $contador++;    
                        }
                    }

                    if($contador  == 0)
                    {
                        $conStr = "mysql:host=localhost;dbname=la_comanda";
                        $user ="yo";
                        $pass ="cp35371754";
                        $estado = "cerrado";
                        $pdo = new PDO($conStr,$user,$pass);
                        $sentencia = $pdo->prepare("UPDATE ordenes SET estado = :estado WHERE dni = :dni");
                        $sentencia->bindValue(':dni', $dni);
                        $sentencia->bindValue(':estado',$estado);

                        if($sentencia->execute())
                        {
                                                
                            if(logs::registroAcciones($registroUsuario,$idServicio,$accion,$fecha) && Mesa :: guardarMesa($idServicio,$orden,$total,$dni,$fecha,$estado))
                            {
                                $payload = json_encode(array("mensaje"=>"cuenta pagada y cerrada el cliente $dni abono un total de $total\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                            else
                            {
                                $payload = json_encode(array("mensaje"=>"error al guardar el registro de accion de empleado\ncliente: ".$nombre." ".$apellido." ya existia, fue asignado una nueva clave con exito\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                        }
                    }
                    else
                    {
                        $payload = json_encode(array("mensaje"=>" el cliente tiene pedidos pendientes\n"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                }
                else
                {
                    $payload = json_encode(array("mensaje"=>" error al cambiar el estado del cliente\n"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                }
                    
            }
            else
            {
                $payload = json_encode(array("mensaje"=>"Error de autenticacion"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
       
    }

    public static function administrarEmpleado($request, $response, $args)
    {
        try
        {
            $modo = token :: decodificarToken($request);
            $registroUsuario = token :: insertarRegistro($request);
            $accion = $request->getUri()->getPath();
            $parametros = $request->getParsedBody();
            $dni = $parametros["dni"];
            $fecha = $parametros["fecha"];

            if($modo == "socio")
            {
                $aux = Control:: traerEmpleado($dni);
                $nombre = $aux["nombre"];
                $apellido = $aux["apellido"];

                if($aux["estado"] == "activo" && $aux["categoria"] != "socio")
                {
                    $condicion = "despedido";
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="yo";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
                    $sentencia = $pdo->prepare("UPDATE staff SET estado = :condicion WHERE dni = :dni");
                    $sentencia->bindValue(':condicion',  $condicion);
                    $sentencia->bindValue(':dni', $dni);
        
            
                    if($sentencia->execute())
                    {
                        if(logs::registroAcciones($registroUsuario,$dni,$accion,$fecha))
                            {
                                $payload = json_encode(array("mensaje"=>"$nombre $apellido $dni fue despedido"));
                                $response->getBody()->write($payload);
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                            else
                            {
                                $payload = json_encode(array("mensaje"=>"error al guardar el registro de accion de empleado\ncliente: ".$nombre." ".$apellido." ya existia, fue asignado una nueva clave con exito\n"));
                                $response->getBody()->write($payload);
                                $pdo = null;
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                    ;
                    }
                }
                else
                {
                    $payload = json_encode(array("mensaje"=>"$nombre $apellido $dni no se encuentra activo"));
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }
            else
            {
                $payload = json_encode(array("mensaje"=>"Error de autenticacion"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }

    public static function verificarPedidosCliente($dni)//para saber pedidos q tengo q preparar como cocinero/bartender
    {                                                                    
        try 
        {

            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="yo";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);
            $sentencia = $pdo->prepare('SELECT pedido FROM ordenes where dni = :condicion');
            $sentencia->bindValue(':condicion', $dni);

    
            if($sentencia->execute())
            {
                $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                $pdo = null;
                return $resultado;
            }
            
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }

    public static function traeridServiciosCliente($dni)
    {

        try
        {
        if($dni>0)
        {

            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="yo";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare("SELECT * FROM ordenes WHERE dni = :dni");
            $sentencia->bindValue(':dni', $dni);

            if($sentencia->execute())
            {
                $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                $array = (array)json_decode($resultado["idServicio"]);
                return $array;
            }
        }
        else
        {
            $payload = json_encode(array("mensaje"=>"error en buscar los registros de los servicios del cliente\n"));
                $response->getBody()->write($payload);
                $pdo = null;
                return $response->withHeader('Content-Type', 'application/json');
        }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }

    }

    public static function estadoCliente($dni)
    {
        try{
        if($dni>0)
        {

            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="yo";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare("SELECT estado FROM ordenes WHERE dni = :dni");
            $sentencia->bindValue(':dni', $dni);

            if($sentencia->execute())
            {
                $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                return $resultado;
            }
        }
        else
        {
            $payload = json_encode(array("mensaje"=>"error en buscar los registros de los servicios del cliente\n"));
                $response->getBody()->write($payload);
                $pdo = null;
                return $response->withHeader('Content-Type', 'application/json');
        }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }

    public static function mostrarAccionesEmpleado($request, $response, $args)
    {
        try
        {
            $modo = token :: decodificarToken($request);
            $parametros = $request->getParsedBody();
            $dni = $parametros["dni"];

            if($modo == "socio")
            {
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="yo";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
                $sentencia = $pdo->prepare("SELECT * FROM logs WHERE dni = :dni");
                $sentencia->bindValue(':dni', $dni);
                if($sentencia->execute())
                {

                    $resultado = $sentencia->fetchALL(PDO :: FETCH_ASSOC);

                    print_r($resultado);

                    $payload = json_encode(array("mensaje"=>"acciones mostradas correctamente\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');
                    
                }
            }
            else
            {
                $payload = json_encode(array("mensaje"=>"error de autenticacion\n"));
                $response->getBody()->write($payload);
                $pdo = null;
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }

    public static function evaluar_servicio($request, $response, $args)
    {
        try
        {
            $parametros = $request->getQueryParams();
            $dni  = $parametros["dni"];
            $notaComida = $parametros["notaComida"];
            $notaRestaurante = $parametros["notaRestaurante"];
            $notaMozo = $parametros["notaMozo"];

            if(is_null($parametros["comentarios"]))
            {
                $comentarios = "sin comentarios";
            }
            else
            {
                $comentarios = $parametros["comentarios"];
            }

            $cliente = Control :: retornarClaveAlfa($dni);//me devuelve el cliente

            $aux2= $cliente["idServicio"];

            $listaOrdenes = (array)json_decode($aux2,true);
            $index = count($listaOrdenes);
            if($index > 1)
            {
                $orden = $listaOrdenes[$index-1];
            }
            else
            {
                $orden = $listaOrdenes[0];
            }

            $aux= $cliente["evaluacion"];

            $listaEvaluaciones = (array)json_decode($aux,true);

            $evaluacion = array("idServicio"=>$orden,
                            "nota_restaurante"=>$notaRestaurante,
                            "nota_comida"=>$notaComida,
                            "nota_mozo"=>$notaMozo,
                            "comentarios"=>$comentarios);
        
            array_push($listaEvaluaciones,$evaluacion);
            
            $modificacionE = json_encode($listaEvaluaciones,true);
            
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="yo";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);
            $sentencia = $pdo->prepare("UPDATE ordenes SET evaluacion = :evaluacion  WHERE dni = :dni");
            $sentencia->bindValue(':evaluacion',$modificacionE);
            $sentencia->bindValue(':dni', $parametros["dni"]);

            if($sentencia->execute())
            {
                if(Mesa :: guardarEvaluacion($orden,$evaluacion))
                {
                    $payload = json_encode(array("mensaje"=>"gracias por sus comentarios\n"));
                    $response->getBody()->write($payload);
                    $pdo = null;
                    return $response->withHeader('Content-Type', 'application/json');
                }
                
            }
            else
            {
                $payload = json_encode(array("mensaje"=>"No se pudo realizar al evaluacion\n"));
                $response->getBody()->write($payload);
                $pdo = null;
                return $response->withHeader('Content-Type', 'application/json');
            }
            
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }
   
}

?>