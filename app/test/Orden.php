<?php

class Orden extends Persona
{
    public $nombre;
    public $apellido;
    public $total;
    public $idServicio;//json -> nulo
    public $pedidos;//json -> nulo
    public $estado;//esperando,cominedo,pagando,cerrado
    public $evaluacion;//json -> nulo

    /*
    public function __construct($nombre,$apellido,$dni,$mail,$categoria,$total,$idServicio,$pedidos,$estado,$evaluacion) {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->dni = $dni;
        $this->mail = $mail;
        $this->categoria = $categoria;
        $this->total = $total;
        $this->idServicio = $idServicio;//array
        $this->pedidos = $pedidos;//array
        $this->estado = $estado;
        $this->evaluacion = $evaluacion;//array
    }
    */

    public static function ExistenciaCliente($nombre,$apellido,$dni,$mail)
    {
      
        
        if(isset($nombre) && isset($apellido) && isset($dni) && isset($mail))
        {
            $asunto = "ordenes";
            $listadoClientes =  Control :: traerTodo($asunto);
            $control = false;
            
            foreach($listadoClientes as $c)
            {
                if($c["nombre"] == $nombre && $c["apellido"] == $apellido && $c["dni"] == $dni && $c["mail"] == $mail)
                {
                    $control = true;
                    
                    if($c["estado"] != "cerrado" || empty($c["estado"]))
                    {
                        $control = "warning";

                    }
                    if($c["estado"] == "cerrado")
                    {
                        $control = "volvio";
                    }
                    break;
                }
            }

            return $control;
        }

    }

    public static function moverImagen($archivo,$dni,$idServicio)
    {

        if(isset($archivo))
        {   
            $file = $archivo->getClientFilename();
            $tempName = $archivo->getStream()->getMetadata('uri');
            $destino = "C:/xampp\htdocs\Slim\app\imagenes_clientes/".$file;
            $nuevoNombre = "$dni"."$idServicio".".jpg";


            if(move_uploaded_file($tempName, $destino))
            {
                echo "\nSe movio exitosamente la foto\n";
                if(rename($destino, 'C:\xampp\htdocs\Slim\app\imagenes_clientes/'.$nuevoNombre))
                {
                    echo "\nSe cambio el nombre de la foto\n";
                    $destinoFinal ='C:\xampp\htdocs\Slim\app\imagenes_clientes/'.$nuevoNombre;
                    return $destinoFinal;
                }
            }
            else
            {
                echo "no se movio la imagen\n";
            }
            
        }
        else
        {
            echo "Ocurrio un error con la imagen";
        }
    }

    public static function administrarPedidos($request, $response,$arg)
    {
        
    }


}


?>