<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

class ProductoController extends Producto implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $titulo = $parametros['titulo'];
        $precio = $parametros['precio'];
        $tipo = $parametros['tipo'];
        $anioSalida = $parametros['anioSalida'];
        $formato = $parametros['formato'];
        $stock = $parametros['stock'];

        $producto = new Producto();
        $producto->titulo = $titulo;
        $producto->precio = $precio;
        $producto->tipo = $tipo;
        $producto->anioSalida = $anioSalida;
        $producto->formato = $formato;
        $producto->stock = $stock;
        $producto->crearProducto();

        $payload = json_encode(array("mensaje" => "Producto creado con exito"));

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
