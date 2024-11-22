<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;

class ValidarDatosVenta 
{    
    private $camposAValidar = array();

    public function __construct($camposAValidar)
    {
        $this->camposAValidar = $camposAValidar;
    }

    public function __invoke(Request $request, RequestHandler $requestHandler)
    {
        $response = new ResponseClass();
        $params = $request->getQueryParams();
        $paramsPost = $request->getParsedBody();
        
        if($params != null)
        {
            foreach($this->camposAValidar as $key => $value)
            {            
                if(!isset($params[$value]))
                {
                    $response->getBody()->write(json_encode(array("error" => "Error. Faltan datos $value")));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
                }           
            }
        }
        if($paramsPost != null)
        {
            foreach($this->camposAValidar as $key => $value)
            {            
                if(!isset($paramsPost[$value]))
                {
                    $response->getBody()->write(json_encode(array("error" => "Error. Faltan datos $value")));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
                }           
            }
        }            
        if($this->camposAValidar == array("mail", "titulo", "tipo", "formato", "cantidad"))
        {
            return $this->ValidarDatosAltaVenta($request, $requestHandler, $response);
        }        
        else if($this->camposAValidar == array("fecha"))
        {
            return $this->ValidarDatosFechaVenta($request, $requestHandler, $response);
        }
        else if($this->camposAValidar == array("usuario"))
        {
            return $this->ValidarDatosUsuario($request, $requestHandler, $response);
        }
        
    }  
    
    public function ValidarDatosAltaVenta(Request $request, RequestHandler $requestHandler, $response)
    {
        $params = $request->getParsedBody();   

        if(isset($params["mail"]) && isset($params["titulo"]) && isset($params["tipo"]) && isset($params["formato"]) && isset($params["cantidad"]))
        {            

            $mailIngresado = $params["mail"]; 
            $tituloIngresado = $params["titulo"]; 
            $tipoIngresado = $params["tipo"]; 
            $formatoIngresado = $params["formato"]; 
            $cantidadIngresada = $params["cantidad"]; 

            if(is_numeric($cantidadIngresada) && $cantidadIngresada > 0)
            {
                if(filter_var($mailIngresado, FILTER_VALIDATE_EMAIL) && is_string($tituloIngresado) &&  is_string($tipoIngresado) && is_string($formatoIngresado) && ($formatoIngresado == 'Digital' || $formatoIngresado == 'Fisico') && ($tipoIngresado == 'Videojuego' || $tipoIngresado == 'Pelicula')) 
                {
                    $tienda = Tienda::obtenerTiendaPorTituloTipoYFormato($tituloIngresado,$tipoIngresado,$formatoIngresado);
                    if($tienda != null && $tienda->stock > $cantidadIngresada && $tienda->stock > 0)
                    {
                        return $requestHandler->handle($request);
                    }
                    $response->getBody()->write(json_encode(array("error" => "Error. El producto de la tienda ingresado no existe o no tiene stock")));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
                }
                else
                {
                    $response->getBody()->write(json_encode(array("error" => "Datos ingresados invalidos")));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
                }                
            }
            else
            {
                $response->getBody()->write(json_encode(array("error" => "Stock ingresado invalido")));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            
        }           
        else
        {
            $response->getBody()->write(json_encode(array("error" => "Cargue los parametros")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        return $response;   
    }
    public function ValidarDatosFechaVenta(Request $request, RequestHandler $requestHandler, $response)
    {
        $params = $request->getQueryParams();           

        if(isset($params["fecha"]))
        {
            $fechaIngresada = $params["fecha"];         
            $fechaFormateada = DateTime::createFromFormat('Y-m-d', $fechaIngresada);  
            $fechaFormateada = $fechaFormateada->format('Y-m-d');
            if ($fechaFormateada != null) 
            {
                return $requestHandler->handle($request);
            }             
        }
        else
        {            
            return $requestHandler->handle($request);
        }          
    }   
    public function ValidarDatosUsuario(Request $request, RequestHandler $requestHandler, $response)
    {
        $params = $request->getQueryParams();           

        if(isset($params["usuario"]))
        {
            $usuarioIngresado = $params["usuario"];         
            if (filter_var($usuarioIngresado, FILTER_VALIDATE_EMAIL)) 
            {
                return $requestHandler->handle($request);
            } 
            else
            {
                $response->getBody()->write(json_encode(array("error" => "Datos ingresados invalidos")));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }            
        }
        else
        {            
            $response->getBody()->write(json_encode(array("error" => "No ingreso datos")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        return $response;         
    }   
    
}
?>