<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;

class ValidarDatosTienda 
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
            return $this->ValidarDatosAltaTienda($request, $requestHandler, $response);
        }
        else if($this->camposAValidar == array("titulo", "tipo", "formato"))
        {
            return $this->ValidarDatosConsultarTienda($request, $requestHandler, $response);
        }    
        else if($this->camposAValidar == array("titulo", "tipo", "precio", "stock"))
        {
            return $this->ValidarSiLaTiendaExiste($request, $requestHandler, $response);
        }  
        else if($this->camposAValidar == array("tipo"))
        {
            return $this->ValidarDatosTipo($request, $requestHandler, $response);
        }  
        else if($this->camposAValidar == array("precio1", "precio2"))
        {
            return $this->ValidarDatosPrecios($request, $requestHandler, $response);
        }  
        
    }  
    public function ValidarDatosAltaTienda(Request $request, RequestHandler $requestHandler, $response)
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
    public function ValidarDatosConsultarTienda(Request $request, RequestHandler $requestHandler, $response)
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
                $response->getBody()->write(json_encode(array("mensaje" => "No hay producto de la tienda del formato " . $formatoIngresado)));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            else if($tipoIngresado != 'Videojuego' && $tipoIngresado != 'Pelicula')
            {
                $response->getBody()->write(json_encode(array("mensaje" => "No hay producto de la tienda del tipo " . $tipoIngresado)));
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
     
    public function ValidarSiLaTiendaExiste(Request $request, RequestHandler $requestHandler, $response)
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
                $tienda = Tienda::obtenerTiendaPorTituloYTipo($tituloIngresado, $tipoIngresado);
                if($tienda != null)
                {
                    $tienda->stock = $tienda->stock + $stockIngresado; 
                    $tienda->precio = $precioIngresado;
                    $response->getBody()->write(json_encode(array("mensaje" => "El producto de la tienda ya existe. Se modifico el stock y el precio")));
                    $tienda->modificarTienda();
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
            $response->getBody()->write(json_encode(array("error" => "Error. Datos ingresados invalidos")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        return $response;   
    }     
    public function ValidarDatosTipo(Request $request, RequestHandler $requestHandler, $response)
    {
        $params = $request->getQueryParams();           

        if(isset($params["tipo"]))
        {
            $tipoIngresado = $params["tipo"];         
            if (is_string($tipoIngresado) && ($tipoIngresado == 'Pelicula' || $tipoIngresado == 'Videojuego')) 
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
    public function ValidarDatosPrecios(Request $request, RequestHandler $requestHandler, $response)
    {
        $params = $request->getQueryParams();           

        if(isset($params["precio1"]) && isset($params["precio2"]))
        {
            $precio1Ingresado = $params["precio1"];         
            $precio2Ingresado = $params["precio2"];         
            if (is_numeric($precio1Ingresado) && is_numeric($precio2Ingresado)) 
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