<?php
require_once './models/Tienda.php';
require_once './models/Venta.php';
require_once './interfaces/IApiUsable.php';

class VentaController extends Venta implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $uploadedFiles = $request->getUploadedFiles();
      $titulo = $parametros['titulo'];
      $tipo = $parametros['tipo'];
      $formato = $parametros['formato'];
      $cantidad = $parametros['cantidad'];

      $mail = $parametros['mail'];
      $numeroPedido = Venta::obtenerNumeroPedido();
      $imagen = $uploadedFiles['imagenUsuario'];

      $tienda = Tienda::obtenerTiendaPorTituloTipoYFormato($titulo,$tipo,$formato);
      if($tienda != null)
      {
        $directorioImagenes = __DIR__ . '/../ImagenesDeVenta/2024/';

        if (!file_exists($directorioImagenes)) 
        {
          if (!mkdir($directorioImagenes, 0777, true))
          {
            $payload = json_encode(array("mensaje" => "No se pudo crear el directorio para guardar las imágenes"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
          }
        }
        $mailSinArroba = explode("@",$mail);      

        $nombreImagen = $titulo . '_' . $tipo . '_' . $formato . '_' . $mailSinArroba[0] . '_' . uniqid() . '.' . pathinfo($imagen->getClientFilename(), PATHINFO_EXTENSION);

        try 
        {
          $imagen->moveTo($directorioImagenes . $nombreImagen);
        } 
        catch (Exception $e) 
        {
          $payload = json_encode(array("mensaje" => "Error al guardar la imagen del cliente" . $e->getMessage()));
          $response->getBody()->write($payload);
          return $response->withHeader('Content-Type', 'application/json');
        }

        $tienda->stock = $tienda->stock - $cantidad;
        $tienda->modificarTienda();
        $venta = new Venta();       
        $venta->mail = $mail;
        $venta->idTienda = $tienda->id;
        $venta->numeroPedido = $numeroPedido;
        $venta->cantidad = $cantidad;
        $venta->crearVenta();

        $payload = json_encode(array("mensaje" => "Venta creada con exito"));
      }      
      else
      {
        $payload = json_encode(array("mensaje" => "Producto de la tienda inexistente"));
      }
      
      
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
      $parametros = $request->getQueryParams();

      $usuario = $parametros['usuario'];
      
      $venta = Venta::obtenerVentasPorUsuario($usuario);
      if($venta != null)
      {
        $payload = json_encode(array("ventas" => $venta));
      }
      else
      {
        $payload = json_encode(array("mensaje" => "VENTAS CON EL USUARIO $usuario NO EXISTEN"));
      }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
      $lista = Venta::obtenerTodos();
      $payload = json_encode(array("listaDeVentas" => $lista));

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }    
    public function TraerProductosVendidos($request, $response, $args)
    {
      $parametros = $request->getQueryParams();

      if(isset($parametros["fecha"]))
      {
        $fecha = $parametros["fecha"];
        $lista = Venta::obtenerProductosVendidosPorFecha($fecha);
        $payload = json_encode(array("Cantidad Productos Vendidos en la Fecha $fecha" => $lista));
        
      }
      else
      {
        $fechaActual = new DateTime(date('Y-m-d'));
        $fechaActual->modify('-1 day');
        $fechaActual = date_format($fechaActual,'Y-m-d');
        $lista = Venta::obtenerProductosVendidosPorFecha($fechaActual);  
        $payload = json_encode(array("Cantidad Productos Vendidos en la Fecha $fechaActual" => $lista));
        
      }
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }    
    public function TraerPorTipoDeTienda($request, $response, $args)
    {
      $parametros = $request->getQueryParams();      
      $tipo = $parametros["tipo"];
      $lista = Venta::obtenerVentasPorTipo($tipo);
      if($lista != null)
      {
        $payload = json_encode(array("Listado de ventas por tipo $tipo" => $lista));
      }
      else
      {
        $payload = json_encode(array("mensaje" => "VENTAS CON EL TIPO $tipo NO EXISTEN"));
      }     
      
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }    
    public function TraerPorRangoPrecio($request, $response, $args)
    {
      $parametros = $request->getQueryParams();
      $precio1 = $parametros['precio1'];
      $precio2 = $parametros['precio2'];

      if($precio1 > $precio2)
      {
        $lista = Tienda::obtenerTiendasPorRangoDePrecios($precio2, $precio1);
      }
      else
      {
        $lista = Tienda::obtenerTiendasPorRangoDePrecios($precio1, $precio2); 
      }

      $payload = json_encode(array("listaDeProductosDeTiendaPorRangoPrecio" => $lista));

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }   
    public function TraerPorAnioSalida($request, $response, $args)
    {
      $lista = Venta::obtenerVentasOrdenadasPorAnioSalida();
      $payload = json_encode(array("listaDeVentasOrdenadasPorAnioSalida" => $lista));

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    } 
    

    
    public function ModificarUno($request, $response, $args)
    {
      $parametros = $request->getParsedBody();

      $venta = Venta::obtenerVentasPorNumeroPedido($parametros['numeroPedido']);
      $titulo = $parametros['titulo'];
      $tipo = $parametros['tipo'];
      $formato = $parametros['formato'];
      if($venta != null)
      {
        if($venta->cantidad > $parametros['cantidad'])
        {
          $tienda = Tienda::obtenerTiendaPorTituloTipoYFormato($titulo,$tipo,$formato);
          if($tienda->id == $venta->idTienda)
          {
            $tienda->stock = $tienda->stock - ($parametros['cantidad'] - $venta->cantidad);
            $tienda->modificarTienda();
            $venta->mail = $parametros['mail'];
            $venta->idTienda = $tienda->id;
            $venta->numeroPedido = $parametros['numeroPedido'];
            $venta->cantidad = $parametros['cantidad'];
          }
          else
          {
            $tiendaDeLaVenta = Tienda::obtenerTiendaPorId($venta->idTienda);
            $tiendaDeLaVenta->stock = $tiendaDeLaVenta->stock + $venta->cantidad;
            $tienda->modificarTienda();
            $tienda->stock = $tienda->stock + ($venta->cantidad - $parametros['cantidad']);
            $tienda->modificarTienda();
            $venta->mail = $parametros['mail'];
            $venta->idTienda = $tienda->id;
            $venta->numeroPedido = $parametros['numeroPedido'];
            $venta->cantidad = $parametros['cantidad'];
          }
  
        }
        else
        {
          $tienda = Tienda::obtenerTiendaPorTituloTipoYFormato($titulo,$tipo,$formato);
          if($tienda->id == $venta->idTienda)
          {
            $tienda->stock = $tienda->stock + ($venta->cantidad - $parametros['cantidad']);
            $tienda->modificarTienda();
            $venta->mail = $parametros['mail'];
            $venta->idTienda = $tienda->id;
            $venta->numeroPedido = $parametros['numeroPedido'];
            $venta->cantidad = $parametros['cantidad'];  
          }
          else
          {
            $tiendaDeLaVenta = Tienda::obtenerTiendaPorId($venta->idTienda);
            $tiendaDeLaVenta->stock = $tiendaDeLaVenta->stock + $venta->cantidad;
            $tiendaDeLaVenta->modificarTienda();
            $tienda->stock = $tienda->stock + ($venta->cantidad - $parametros['cantidad']);
            $tienda->modificarTienda();
            $venta->mail = $parametros['mail'];
            $venta->idTienda = $tienda->id;
            $venta->numeroPedido = $parametros['numeroPedido'];
            $venta->cantidad = $parametros['cantidad'];
          }
        }
        $venta->modificarVenta();

        $payload = json_encode(array("mensaje" => "La venta se modificado con exito"));
      }
      else
      {
        $payload = json_encode(array("mensaje" => "EL NUMERO DE PEDIDO INGRESADO ES INEXISTENTE"));
      }       

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
      $parametros = $request->getParsedBody();

      $venta = Venta::obtenerVentasPorNumeroPedido($parametros['numeroPedido']);

      if($venta != null)
      {
        Venta::borrarVenta($venta);
        $payload = json_encode(array("mensaje" => "La venta fue borrada con exito"));
      }
      else
      {
        $payload = json_encode(array("mensaje" => "EL NUMERO DE PEDIDO INGRESADO ES INEXISTENTE"));
      }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }
}
