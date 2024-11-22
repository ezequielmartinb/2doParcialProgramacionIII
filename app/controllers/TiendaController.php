<?php
require_once './models/Tienda.php';
require_once './interfaces/IApiUsable.php';

class TiendaController extends Tienda implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $uploadedFiles = $request->getUploadedFiles();

      $titulo = $parametros['titulo'];
      $precio = $parametros['precio'];
      $tipo = $parametros['tipo'];
      $anioSalida = $parametros['anioSalida'];
      $formato = $parametros['formato'];
      $stock = $parametros['stock'];
      $imagen = $uploadedFiles['imagen'];

      $directorioImagenes = __DIR__ . '/../ImagenesDeProductos/2024/';

      if (!file_exists($directorioImagenes)) 
      {
        if (!mkdir($directorioImagenes, 0777, true))
        {
          $payload = json_encode(array("mensaje" => "No se pudo crear el directorio para guardar las imágenes"));
          $response->getBody()->write($payload);
          return $response->withHeader('Content-Type', 'application/json');
        }
      }

      $nombreImagen = $tipo . '_' . $titulo . '_' . uniqid() . '.' . pathinfo($imagen->getClientFilename(), PATHINFO_EXTENSION);

      try 
      {
        $imagen->moveTo($directorioImagenes . $nombreImagen);
      } 
      catch (Exception $e) 
      {
        $payload = json_encode(array("mensaje" => "Error al guardar la imagen del producto de la tienda" . $e->getMessage()));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
      }

      $tienda = new Tienda();
      $tienda->titulo = $titulo;
      $tienda->precio = $precio;
      $tienda->tipo = $tipo;
      $tienda->anioSalida = $anioSalida;
      $tienda->formato = $formato;
      $tienda->stock = $stock;
      $tienda->crearTienda();

      $payload = json_encode(array("mensaje" => "Producto de la tienda creado con exito"));

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
      $parametros = $request->getParsedBody();

      $titulo = $parametros['titulo'];
      $tipo = $parametros['tipo'];
      $formato = $parametros['formato'];
      $tienda = Tienda::obtenerTiendaPorTituloTipoYFormato($titulo, $tipo, $formato);
      if($tienda != null)
      {
        $payload = json_encode(array("mensaje" => "existe"));
      }
      else
      {
        $payload = json_encode(array("mensaje" => "EL PRODUCTO DE LA TIENDA INGRESADO NO EXISTE"));
      }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
      $lista = Tienda::obtenerTodos();
      $payload = json_encode(array("listaDeTienda" => $lista));

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

      $payload = json_encode(array("listaDeProductosPorRangoPrecio" => $lista));

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }
    public function TraerProductoMasVendido($request, $response, $args)
    {
      $lista = Tienda::obtenerProductosMasVendido();
      $payload = json_encode(array("listaDeProductoMasVendido" => $lista));

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }
    public function ModificarUno($request, $response, $args)
    {
      $parametros = $request->getParsedBody();

      $tienda = Tienda::obtenerTiendaPorId($parametros['id']);

      if($tienda != null)
      {
        $tienda->titulo = $parametros['titulo'];
        $tienda->precio = $parametros['precio'];
        $tienda->tipo = $parametros['tipo'];
        $tienda->anioSalida = $parametros['anioSalida'];
        $tienda->formato = $parametros['formato'];
        $tienda->stock = $parametros['stock'];

        $tienda->modificarTienda();

        $payload = json_encode(array("mensaje" => "El Producto de la Tienda modificado con exito"));
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

      $tienda = Tienda::obtenerTiendaPorId($parametros['id']);

      if($tienda != null)
      {
        $tienda->borrarTienda();
        $payload = json_encode(array("mensaje" => "El producto de la tienda fue borrado con exito"));
      }
      else
      {
        $payload = json_encode(array("mensaje" => "EL ID INGRESADO ES INEXISTENTE"));
      }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }
}
