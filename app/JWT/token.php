<?php

use Firebase\JWT\JWT;
use Firebase\JWT\key;

class token{


    public static function crearToken($dni,$categoria){

        $ahora = time();
        $identificador = "COMANDA API 2023";
        $payload = array(
            'iat' => $ahora,
            'exp' => $ahora + (60000)*24*90,
            'app' => $identificador,
            'DNI' => $dni,
            'categoria' => $categoria
        );

        return JWT::encode($payload,"SCHry169","HS256");
    }

    public static function obtenerCategoria($token)
    {
        try
        {
            return JWT::decode($token,"SCHry169",['HS256'])->categoria;
        }
        catch (Exception $e) {
            $response->getBody()->write(json_encode(array('error - Token invalido' => $e->getMessage())));
        }
    }

    public static function decodificarToken($request)
    {
        $header = $request->getHeaderLine('Authorization');
        if(!empty($header))
        {
            $token = trim(explode("Bearer", $header)[1]);
            $categoria = JWT::decode($token,"SCHry169",['HS256'])->categoria;
            $registro = JWT::decode($token,"SCHry169",['HS256'])->DNI;

            $aux = Control:: traerEmpleado($registro);

            if(($categoria == "socio" || $categoria == "mozo" || $categoria == "cocinero" || 
                $categoria == "bartender" || $categoria == "cervecero") && $aux["estado"] == "activo")
            {
                return $categoria;
            }
            else
            {
                throw new Exception("el token pertenece a una persona despedida");

            }
        }
        else
        {                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                
            throw new Exception("Token vacío");
        }        
       
    }

    public static function insertarRegistro($request)
    {
        $header = $request->getHeaderLine('Authorization');
        if(!empty($header))
        {
            $token = trim(explode("Bearer", $header)[1]);
            $registro = JWT::decode($token,"SCHry169",['HS256'])->DNI;

            $aux = Control:: traerEmpleado($registro);
            

            if($registro>0 && $aux["estado"] == "activo")
            {
                return $registro;
            }
            else
            {
                throw new Exception("el token pertenece a una persona despedida");

            }
        }
        else
        {                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                
            throw new Exception("Token vacío");
        }        
       
    }
    


}