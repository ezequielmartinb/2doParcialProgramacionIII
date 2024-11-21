<?php

class Venta
{
    public $id;
    public $idProducto;
    public $mail;
    public $numeroPedido;
    public $fechaAlta;    

    public function crearVenta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO ventas (idProducto, mail, numeroPedido, fechaAlta) 
        VALUES (:idProducto, :mail, :numeroPedido, :fechaAlta)");
        $fechaAlta = new DateTime(date('Y-m-d'));
        $consulta->bindValue(':idProducto', $this->idProducto, PDO::PARAM_INT);
        $consulta->bindValue(':mail', $this->mail, PDO::PARAM_STR);
        $consulta->bindValue(':numeroPedido', $this->numeroPedido, PDO::PARAM_STR);
        $consulta->bindValue(':fechaAlta', date_format($fechaAlta,'Y-m-d'), PDO::PARAM_STR);      
        
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, idProducto, mail, numeroPedido, fechaAlta FROM ventas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function obtenerVentasPorId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, idProducto, mail, numeroPedido, fechaAlta FROM ventas WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);        
        
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }   

    public static function modificarVenta($venta)
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
    public static function obtenerNumeroPedido() 
    {        
        $letras = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numeros = '0123456789';

        $numeroDePedido = $letras[rand(0, strlen($letras) - 1)];
        for ($i = 2; $i < 6; $i++) 
        {
            $caracteres = rand(0, 1) ? $letras : $numeros;
            $numeroDePedido .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }

        $numeroDePedido = str_shuffle($numeroDePedido);

        return $numeroDePedido;
    }
}