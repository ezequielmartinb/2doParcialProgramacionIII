<?php

class Tienda
{
    public $id;
    public $titulo;
    public $precio;
    public $tipo;
    public $anioSalida;
    public $formato;
    public $stock;

    public function crearTienda()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO tienda (titulo, precio, tipo, anioSalida, formato, stock) 
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
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, titulo, precio, tipo, anioSalida, formato, stock FROM tienda");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Tienda');
    }
    public static function obtenerTiendaPorId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, titulo, precio, tipo, anioSalida, formato, stock FROM tienda 
        WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);        
        
        $consulta->execute();

        return $consulta->fetchObject('Tienda');
    }
    public static function obtenerTiendaPorTituloTipoYFormato($titulo, $tipo, $formato)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, titulo, precio, tipo, anioSalida, formato, stock FROM tienda 
        WHERE titulo = :titulo AND tipo = :tipo AND formato = :formato");
        $consulta->bindValue(':titulo', $titulo, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindValue(':formato', $formato, PDO::PARAM_STR);
        
        $consulta->execute();

        return $consulta->fetchObject('Tienda');
    }
    public static function obtenerTiendaPorTituloYTipo($titulo, $tipo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, titulo, precio, tipo, anioSalida, formato, stock FROM tienda 
        WHERE titulo = :titulo AND tipo = :tipo");
        $consulta->bindValue(':titulo', $titulo, PDO::PARAM_STR);        
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);        
        
        $consulta->execute();

        return $consulta->fetchObject('Tienda');
    }    
    public static function obtenerTiendasPorRangoDePrecios($precio1, $precio2)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, titulo, precio, tipo, anioSalida, formato, stock FROM tienda 
        WHERE precio BETWEEN :precio1 AND :precio2");
        $consulta->bindValue(':precio1', $precio1, PDO::PARAM_INT);        
        $consulta->bindValue(':precio2', $precio2, PDO::PARAM_INT);        
        
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Tienda');
    }
    public static function obtenerProductosMasVendido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT t.id, t.titulo, t.precio, t.tipo, t.anioSalida, t.formato, t.stock, SUM(v.cantidad) AS total_vendido 
        FROM tienda t JOIN ventas v ON t.id = v.idTienda 
        GROUP BY t.id, t.titulo, t.precio, t.tipo, t.anioSalida, t.formato, t.stock ORDER BY total_vendido DESC LIMIT 1;");           
        
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS);
    }

    public function modificarTienda()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE tienda SET titulo = :titulo, precio = :precio, tipo = :tipo, anioSalida = :anioSalida, 
        formato = :formato, stock = :stock WHERE id = :id");
       
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':titulo', $this->titulo, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_INT);
        $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
        $consulta->bindValue(':anioSalida', $this->anioSalida, PDO::PARAM_INT);
        $consulta->bindValue(':formato', $this->formato, PDO::PARAM_STR);
        $consulta->bindValue(':stock', $this->stock, PDO::PARAM_INT);
        $consulta->execute();
    }

    public function borrarTienda()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE tienda SET stock = :stock WHERE id = :id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':stock', 0);
        $consulta->execute();
    }
}