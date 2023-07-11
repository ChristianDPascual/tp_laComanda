<?php

function generarCodigoProducto()
{
    $ruta = './archives/idProductos.csv';
    if(filesize($ruta))
    {
        $archivo = fopen($ruta,"r");
        while(!feof($archivo))
        {             
                $valores= explode(',',fgets($archivo));
        }
        fclose($archivo);
        
        $id = crearIDProducto($valores);

        $archivo = fopen($ruta,"a");
        if(fwrite($archivo,"$id,")==0)
        {
             echo "no se pudo guardar el ultimo id<br>";
        }
        fclose($archivo);
        return $id;
        
    }
    else
    {

        $archivo = fopen($ruta,"w");
        $id=rand(1,10000);
        if(fwrite($archivo,"$id,")==0)
        {
            echo "no se pudo guardar el id\n";
        }
         fclose($archivo);
         return $id;
        
    }
}

function crearIDProducto($valores)
{
    if(isset($valores))
    {
        do{
            $nuevoID = rand(1,10000);
            $control = 1;
            foreach($valores as $valor)
            {
                if($nuevoID == $valor)
                {
                    $control=0;
                    break;
                }
            }
        }while($control==0);

        return $nuevoID;
    }
    else
    {
        echo "Ocurrio un error al generar un nuevo ID<br>";

    }
}

function controlID()
{  
    $ruta = "./archives/idAlfanumericos.csv";
    if(filesize($ruta))
    {
        $archivo = fopen($ruta,"r");
        while(!feof($archivo))
        {             
                $valores= explode(',',fgets($archivo));
        }
        fclose($archivo);
        
        $id = crearID($valores);

        $archivo = fopen($ruta,"a");
        if(fwrite($archivo,"$id,")==0)
        {
             echo "no se pudo guardar el ultimo id<br>";
        }
        fclose($archivo);
        return $id;
        
    }
    else
    {
        $cadena = '0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';
        $digitos = explode(',',$cadena);
        $id = null;
        $aux;
        
        for($i=0;$i<5;$i++)
        {
            $aux = $digitos[rand(1,36)-1];
            $id = $id.$aux;
        }

        $archivo = fopen($ruta,"w");

        if(fwrite($archivo,"$id,")==0)
        {
            echo "no se pudo guardar el id\n";
        }
         fclose($archivo);
         return $id;
        
    }
}

function crearID($valores)
{
    if(isset($valores))
    {
        do{
            $control = 1;
            $cadena = '0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';
            $digitos = explode(',',$cadena);
            $id = null;
            $aux;
            
            for($i=0;$i<5;$i++)
            {
                $aux = $digitos[rand(1,36)-1];
                $id = $id.$aux;
            }

            foreach($valores as $valor)
            {
                if($id == $valor)
                {
                    $control=0;
                    break;
                }
            }
        }while($control==0);

        return $id;
    }
    else
    {
        echo "Ocurrio un error al generar un nuevo ID<br>";

    }
}

function controlIDVenta($ruta)
{  
    if(filesize($ruta))
    {
        $archivo = fopen($ruta,"r");
        while(!feof($archivo))
        {             
                $valores= explode(',',fgets($archivo));
        }
        fclose($archivo);
        
        $id = autoincrementarID($valores);

        $archivo = fopen($ruta,"a");
        if(fwrite($archivo,"$id,")==0)
        {
             echo "no se pudo guardar el ultimo id<br>";
        }
        fclose($archivo);
        return $id;
        
    }
    else
    {

        $archivo = fopen($ruta,"w");
        $id = 1;
        if(fwrite($archivo,"$id,")==0)
        {
            echo "no se pudo guardar el id\n";
        }
         fclose($archivo);
         return $id;
        
    }
}

function autoincrementarID($valores)
{
    if(isset($valores))
    {
        $nuevoID = sizeof($valores);
        return $nuevoID;
    }
    else
    {
        echo "Ocurrio un error al generar un nuevo ID<br>";

    }
}

function validarNumero($valor)
{
    if(isset($valor) && is_numeric($valor))
    {
        return $valor;
    }
    else
    {
        echo "ingrese un numero\n";
    }
}

function validarCadena($valor)
{
    if(isset($valor) && is_string($valor))
    {
        return $valor;
    }
    else
    {
        echo "ingrese una palabra valida\n";
    }
}

function validarCondicionTipo($valor)
{
    if(isset($valor) && ($valor == "agua" || $valor == "crema"))
    {
        return $valor;
    }
    else
    {
        echo "ingrese un campo valido\n";
    }
}

function validarCondicionVaso($valor)
{
    if(isset($valor) && ($valor == "cucurucho" || $valor == "plastico"))
    {
        return $valor;
    }
    else
    {
        echo "ingrese un campo valido\n";
    }
}

function validarPrecio($valor)
{
    if(isset($valor) && (is_float($valor) || is_numeric($valor)) && $valor>0)
    {
        return $valor;
    }
    else
    {
        echo "ingrese un precio valido\n";
    }
}

function validarArchivos($valor)
{
    if ($valor['name'] != null)
    {
        return $valor;
    }
    else
    {
        echo "no se ingreso una foto valida\n";
    }
}

function validarFecha($valor)
{
        if(isset($valor))
        {
            
            $fecha=date_create_from_format("d-m-Y",$valor);
            $fechaFinal =date_parse_from_format("Y/m/d",date_format($fecha,"Y/m/d"));

            if(checkdate($fechaFinal["month"],$fechaFinal["day"],$fechaFinal["year"]))
            {
                return $fechaFinal;
            }
            else
            {
                echo "ingrese una fecha valida";
            }
        }
        else
        {
            echo "ingrese una fecha\n";
        }
}


?>