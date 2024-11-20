<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;

class ValidarDatosProducto 
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
        if($this->camposAValidar == array("titulo", "precio", "tipo", "anioSalida", "formato", "stock"))
        {
            return $this->ValidarDatosAltaProducto($request, $requestHandler, $response);
        }
        else if($this->camposAValidar == array("titulo", "tipo", "formato"))
        {
            return $this->ValidarDatosConsultarProducto($request, $requestHandler, $response);
        }    
        else if($this->camposAValidar == array("titulo", "tipo", "precio", "stock"))
        {
            return $this->ValidarSiElProductoExiste($request, $requestHandler, $response);
        }  
        
    }  
    public function ValidarDatosAltaProducto(Request $request, RequestHandler $requestHandler, $response)
    {
        $params = $request->getParsedBody();        
        
        if(isset($params["titulo"]) && isset($params["precio"]) && isset($params["tipo"]) && isset($params["anioSalida"])
        && isset($params["formato"]) && isset($params["stock"]))
        {            
            $tituloIngresado = $params["titulo"]; 
            $precioIngresado = $params["precio"];            
            $tipoIngresado = $params["tipo"]; 
            $anioSalidaIngresado = $params["anioSalida"]; 
            $formatoIngresado = $params["formato"]; 
            $stockIngresado = $params["stock"]; 
            if(is_string($tituloIngresado) && is_numeric($precioIngresado) && is_string($tipoIngresado) && is_numeric($anioSalidaIngresado)
            && is_string($formatoIngresado) && is_numeric($stockIngresado) && ($formatoIngresado == 'Digital' || $formatoIngresado == 'Fisico') && 
            ($tipoIngresado == 'Videojuego' || $tipoIngresado == 'Pelicula')) 
            {
                return $requestHandler->handle($request);
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