<?php
require_once './models/Producto.php';
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
      $stock = $parametros['stock'];

      $mail = $parametros['mail'];
      $numeroPedido = Venta::obtenerNumeroPedido();
      $imagen = $uploadedFiles['imagenUsuario'];

      $producto = Producto::obtenerProductosPorTituloTipoFormatoYStock($titulo,$tipo,$formato,$stock);
      
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
        $payload = json_encode(array("mensaje" => "Error al guardar la imagen del producto" . $e->getMessage()));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
      }

      $producto->stock = $producto->stock - 1;
      Producto::modificarProducto($producto);
      $venta = new Venta();
      $venta->idProducto = $producto->id;
      $venta->mail = $mail;
      $venta->numeroPedido = $numeroPedido;
      $venta->crearVenta();

      $payload = json_encode(array("mensaje" => "Venta creada con exito"));
      
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
      $parametros = $request->getParsedBody();

      $titulo = $parametros['titulo'];
      $tipo = $parametros['tipo'];
      $formato = $parametros['formato'];
      $producto = Producto::obtenerProductosPorTituloTipoYFormato($titulo, $tipo, $formato);
      if($producto != null)
      {
        $payload = json_encode(array("mensaje" => "existe"));
      }
      else
      {
        $payload = json_encode(array("mensaje" => "EL PRODUCTO INGRESADO NO EXISTE"));
      }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
      $lista = Producto::obtenerTodos();
      $payload = json_encode(array("listaDeProductos" => $lista));

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
      $parametros = $request->getParsedBody();

      $producto = Producto::obtenerProductosPorId($parametros['id']);

      if($producto != null)
      {
        $producto->titulo = $parametros['titulo'];
        $producto->precio = $parametros['precio'];
        $producto->tipo = $parametros['tipo'];
        $producto->anioSalida = $parametros['anioSalida'];
        $producto->formato = $parametros['formato'];
        $producto->stock = $parametros['precio'];

        Producto::modificarProducto($producto);

        $payload = json_encode(array("mensaje" => "Producto modificado con exito"));
      }
      else
      {
        $payload = json_encode(array("mensaje" => "EL ID INGRESADO ES INEXISTENTE"));
      }       

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
      $parametros = $request->getParsedBody();

      $producto = Producto::obtenerProductosPorId($parametros['id']);

      if($producto != null)
      {
        Producto::borrarProducto($producto);
        $payload = json_encode(array("mensaje" => "El producto fue borrado con exito"));
      }
      else
      {
        $payload = json_encode(array("mensaje" => "EL ID INGRESADO ES INEXISTENTE"));
      }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }
}
