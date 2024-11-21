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
        if($this->camposAValidar == array("mail", "titulo", "tipo", "formato", "stock"))
        {
            return $this->ValidarDatosAltaVenta($request, $requestHandler, $response);
        }        
        
    }  
    public function ValidarDatosAltaVenta(Request $request, RequestHandler $requestHandler, $response)
    {
        $params = $request->getParsedBody();   

        if(isset($params["mail"]) && isset($params["titulo"]) && isset($params["tipo"]) && isset($params["formato"]) && isset($params["stock"]))
        {            

            $mailIngresado = $params["mail"]; 
            $tituloIngresado = $params["titulo"]; 
            $tipoIngresado = $params["tipo"]; 
            $formatoIngresado = $params["formato"]; 
            $stockIngresado = $params["stock"]; 

            if(is_numeric($stockIngresado) && $stockIngresado > 0)
            {
                if(filter_var($mailIngresado, FILTER_VALIDATE_EMAIL) && is_string($tituloIngresado) &&  is_string($tipoIngresado) && is_string($formatoIngresado) && ($formatoIngresado == 'Digital' || $formatoIngresado == 'Fisico') && ($tipoIngresado == 'Videojuego' || $tipoIngresado == 'Pelicula')) 
                {
                    $producto = Producto::obtenerProductosPorTituloTipoFormatoYStock($tituloIngresado,$tipoIngresado,$formatoIngresado,$stockIngresado);
                    if($producto!=null)
                    {
                        return $requestHandler->handle($request);
                    }
                    $response->getBody()->write(json_encode(array("error" => "Error. El producto ingresado no existe")));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
                }                
            }
            else
            {
                $response->getBody()->write(json_encode(array("error" => "No se puede vender un producto sin stock")));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            
        }           
        else
        {
            $response->getBody()->write(json_encode(array("error" => "Error. Datos ingresados invalidos")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        return $response;   
    }    
    public function ValidarDatosConsultarProducto(Request $request, RequestHandler $requestHandler, $response)
    {
        $params = $request->getParsedBody();         
        
        if(isset($params["titulo"]) && isset($params["tipo"]) && isset($params["formato"]))
        {            
            $tituloIngresado = $params["titulo"]; 
            $tipoIngresado = $params["tipo"]; 
            $formatoIngresado = $params["formato"]; 
            
            if(is_string($tituloIngresado) && is_string($tipoIngresado) && is_string($formatoIngresado) && 
            ($formatoIngresado == 'Digital' || $formatoIngresado == 'Fisico') && 
            ($tipoIngresado == 'Videojuego' || $tipoIngresado == 'Pelicula')) 
            {
                return $requestHandler->handle($request);
            }
            else if($formatoIngresado != 'Digital' && $formatoIngresado != 'Fisico')
            {
                $response->getBody()->write(json_encode(array("mensaje" => "No hay producto del formato " . $formatoIngresado)));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            else if($tipoIngresado != 'Videojuego' && $tipoIngresado != 'Pelicula')
            {
                $response->getBody()->write(json_encode(array("mensaje" => "No hay producto del tipo " . $tipoIngresado)));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            
        }           
        else
        {
            $response->getBody()->write(json_encode(array("error" => "Error. Datos ingresados invalidos")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        return $response;   
    }   
     
    public function ValidarSiElProductoExiste(Request $request, RequestHandler $requestHandler, $response)
    {
        $params = $request->getParsedBody();        
        
        if(isset($params["titulo"]) && isset($params["tipo"]) && isset($params["precio"]) && isset($params["stock"]))
        {            
            $tituloIngresado = $params["titulo"]; 
            $tipoIngresado = $params["tipo"]; 
            $precioIngresado = $params["precio"];    
            $stockIngresado = $params["stock"];                    
            if(is_string($tituloIngresado) && is_string($tipoIngresado) && is_numeric($precioIngresado) && is_numeric($stockIngresado)) 
            {
                $producto = Producto::obtenerProductosPorTituloYTipo($tituloIngresado, $tipoIngresado);
                if($producto != null)
                {
                    $producto->stock = $producto->stock + $stockIngresado; 
                    $producto->precio = $precioIngresado;
                    $response->getBody()->write(json_encode(array("mensaje" => "El producto ya existe. Se modificó el stock y el precio")));
                    Producto::modificarProducto($producto);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(202);
                }
                else
                {
                    return $requestHandler->handle($request);
                }
            }
        }           
        else
        {
            $response->getBody()->write(json_encode(array("error" => "Error. Datos ingresados invalidos VALIDAR SI EL PRODUCTO EXISTE")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        return $response;   
    }  
    
    public function ValidarId(Request $request, RequestHandler $requestHandler, $response, $id)
    {
        $params = $request->getParsedBody();
        $idIngresado = $params[$id]; 
        if(!(is_numeric($idIngresado)))
        {
            $response->getBody()->write(json_encode(array("error" => "Error. Datos ingresados invalidos")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        return $requestHandler->handle($request);
    }
}
?>