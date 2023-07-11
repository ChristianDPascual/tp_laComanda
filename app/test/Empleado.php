<?php
require_once('C:\xampp\htdocs\Slim\app\test\Persona.php');
class Empleado extends Persona
{
    public $estado;
    public $registroDias;
    public $historialServicios;

    /*
    public function __construct($nombre,$apellido,$dni,$mail,$categoria,$estado)
    {
        $this->registroDias = array();
        $this-$historialServicios = array();
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->dni = $dni;
        $this->mail=$mail;
        $this->estado = $estado;

    }
    */

    public static function login($request, $response, $args){

        $parametros = $request->getParsedBody();
        $mail = $parametros["mail"];

        $aux = Control :: obtenerDatosLogin($mail);
        

        if($aux["dni"] !== null && $aux["estado"] == "activo"){
            $cat = $aux["categoria"];
            $token = token::crearToken($aux["dni"], $aux["categoria"]);
            $payload = json_encode(array("mensaje" => "OK. $cat", "token" => $token));
        }
        else{
            $payload = json_encode(array("mensaje" => "ERROR en el ingreso de las credenciales $mail"));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }


    public static function ExistenciaStaff($nombre,$apellido,$dni,$mail)
    {
        if(isset($nombre) && isset($apellido) && isset($dni) && isset($mail))
        {
            $asunto = "staff";
            $listadoClientes = Control :: traerTodo($asunto);
            $control = false;

            foreach($listadoClientes as $c)
            {
                if($c["nombre"] == $nombre && $c["apellido"] == $apellido && $c["dni"] == $dni && $c["mail"] == $mail)
                {
                    $control = true;
                    break;
                }
            }

            return $control;
        }

    }




                                                 //viene como put obj json
    public static function realizarOrden($pedido)//se relaciona con la clase Producto
    {                                           //recibe id del producto y servicio, inicia en estado
        if(isset($pedido))                      //solicitado, recibe la fecha y la cantidad que se necesita
        {

            $comanda =json_decode($pedido,true);
            $listaOrdenes = array();
            foreach($comanda as $o)
            {
                if(Producto :: disponibilidadProducto($o["idProducto"]))
                {
                    $aux = array("idProducto"=>$pedido["idProducto"],
                             "fecha"=>$pedido["fecha"],
                             "estado"=>"solicitado",
                             "cantidad"=>$pedido["cantidad"],
                             "idServicio"=>$pedido["idServicio"]);
                array_push($listaOrdenes,$aux);
                }
       
            }

            return $listaOrdenes;//tengo q cargar este archivo en el json de orden de cliente
        }
    }

    public static function cambiarEstadoOrden($ordenes,$pedido)//mando el id y si esta listo el pedido ->cocinero
    {                                                   //mando el id y si el pedido fue entregado->mozo
        if(isset($ordenes) && isset($pedido))
        {
            $contador = 0;
            $control = 0;
            foreach($ordenes as $o)
            {
                if($o["idProducto"] == $pedido["idProducto"] && $o["idServicio"] == $pedido["idServicio"] && $o["cantidad"] == $pedido["cantidad"])
                {
                    $control = 1;
                    break;
                }
                $contador++;
            }

            if($control == 1)
            {
                $ordenes[$contador]["estado"] =$pedido["estado"];
            }

            return $ordenes;//tengo q cargar este archivo en el json de orden
        }
    }

    //reveer esta funcion
    public function guardarServicios($request, $response, $args)
    {
        if(isset($request))
        {
            if(isset($idServicio) && isset($producto) && isset($empleado))
            {
                $servicio = array(
                    "idServicio"=>$idServicio,
                    "idProducto"=>$idProducto,
                    "id"=>$empleado->idEmpleado);
                $archivo = fopen($path,"w");
                fwrite($archivo,json_encode($listado));
                fclose($archivo);
            }
            else
            {
                echo "faltan datos para poder guardar el servicio\n";
            }
                    
        }
        else
        {
            echo "ocurrio un error con guardar el listado en json\n";
        }
        
    }

}


?>