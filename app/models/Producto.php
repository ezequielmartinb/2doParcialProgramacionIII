<?php

class Producto
{
    public $id;
    public $titulo;
    public $precio;
    public $tipo;
    public $anioSalida;
    public $formato;
    public $stock;

    public function crearProducto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (titulo, precio, tipo, anioSalida, formato, stock) 
        VALUES (:titulo, :precio, :tipo, :anioSalida, :formato, :stock)");
        $consulta->bindValue(':titulo', $this->titulo, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_INT);
        $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
        $consulta->bindValue(':anioSalida', $this->anioSalida, PDO::PARAM_INT);
        $consulta->bindValue(':formato', $this->formato, PDO::PARAM_STR);
        $consulta->bindValue(':stock', $this->stock, PDO::PARAM_INT);
        
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, titulo, precio, tipo, anioSalida, formato, stock FROM productos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function obtenerProductosPorTituloTipoYFormato($titulo, $tipo, $formato)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, titulo, precio, tipo, anioSalida, formato, stock FROM productos 
        WHERE titulo = :titulo AND tipo = :tipo AND formato = :formato");
        $consulta->bindValue(':titulo', $titulo, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindValue(':formato', $formato, PDO::PARAM_STR);
        
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }
    public static function obtenerProductosPorTituloTipoFormatoYStock($titulo, $tipo, $formato, $stock)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, titulo, precio, tipo, anioSalida, formato, stock FROM productos 
        WHERE titulo = :titulo AND tipo = :tipo AND formato = :formato AND stock = :stock");
        $consulta->bindValue(':titulo', $titulo, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindValue(':formato', $formato, PDO::PARAM_STR);
        $consulta->bindValue(':stock', $stock, PDO::PARAM_INT);
        
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }
    public static function obtenerProductosPorId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, titulo, precio, tipo, anioSalida, formato, stock FROM productos 
        WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);        
        
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }
    public static function obtenerProductosPorTituloYTipo($titulo, $tipo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, titulo, precio, tipo, anioSalida, formato, stock FROM productos 
        WHERE titulo = :titulo AND tipo = :tipo");
        $consulta->bindValue(':titulo', $titulo, PDO::PARAM_STR);        
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);        
        
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }

    public static function modificarProducto($producto)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET titulo = :titulo, precio = :precio, tipo = :tipo, anioSalida = :anioSalida, formato = :formato, stock = :stock
        WHERE id = :id");
       
        $consulta->bindValue(':id', $producto->id, PDO::PARAM_INT);
        $consulta->bindValue(':titulo', $producto->titulo, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $producto->precio, PDO::PARAM_INT);
        $consulta->bindValue(':tipo', $producto->tipo, PDO::PARAM_STR);
        $consulta->bindValue(':anioSalida', $producto->anioSalida, PDO::PARAM_INT);
        $consulta->bindValue(':formato', $producto->formato, PDO::PARAM_STR);
        $consulta->bindValue(':stock', $producto->stock, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarProducto($producto)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET stock = :stock WHERE id = :id");
        $consulta->bindValue(':id', $producto->id, PDO::PARAM_INT);
        $consulta->bindValue(':stock', 0);
        $consulta->execute();
    }
}