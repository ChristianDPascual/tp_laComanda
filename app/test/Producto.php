<?php

class Producto
{
    public $articulo;
    public $idProducto;
    public $categoria;
    public $precioVenta;

    public function __construct($articulo,$idProducto,$categoria,$precioVenta) {
        $this->articulo = $articulo;
        $this->idProducto = $idProducto;
        $this->categoria = $categoria;
        $this->precioVenta = $precioVenta;
    }

    public static function existenciaProducto($articulo,$precioVenta,$categoria)
    {
        if(isset($categoria) && isset($precioVenta) && isset($articulo))
        {
            $condicion = "productos";
            $lista =  Control :: traerTodo($condicion);
            $retorno = false;


            foreach($lista as $p)
            {

                if($articulo == $p["articulo"] && $precioVenta == $p["precioVenta"] && $categoria == $p["categoria"])
                {
                    $retorno = true;
                    break;
                }
            }
           
            return $retorno;
        }
    }

    public static function disponibilidadProducto($idProducto)
    {
        if(isset($idProducto) && isset($producto))
        {
            $condicion = "productos";
            $lista = traerTodo($condicion);
            $retorno = false;

            foreach($lista as $p)
            {
                if($idProducto= $p["idProducto"] )
                {
                    $retorno = true;
                    break;
                }
            }
            
            return $retorno;
        }
    }

    public static function retornarCategoria($idProducto)
    {
        $retorno = false;

        if(isset($idProducto))
        {
            $opcion ="productos";
            
            $aux = Control :: traerTodo($opcion);

            foreach($aux as $p)
            {
                if($p["idProducto"] == $idProducto)
                {
                    $retorno = $p["categoria"];
                    break;
                }
            }
    

        }

        return $retorno;

    }

    public static function calcularPrecio($idProducto)
    {
        $retorno = false;

        if(isset($idProducto))
        {
            $opcion ="productos";
            
            $aux = Control :: traerTodo($opcion);

            foreach($aux as $p)
            {
                if($p["idProducto"] == $idProducto)
                {
                    $retorno = $p["precioVenta"];
                    break;
                }
            }
    

        }

        return $retorno;

    }

}

?>