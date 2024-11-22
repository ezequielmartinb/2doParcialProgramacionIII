<?php

class Venta
{
    public $id;
    public $mail;
    public $idTienda;
    public $numeroPedido;
    public $cantidad;
    public $fechaAlta;

    public function crearVenta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO ventas (mail, idTienda, numeroPedido, cantidad, fechaAlta) 
        VALUES (:mail, :idTienda, :numeroPedido, :cantidad, :fechaAlta)");
        $fechaAlta = new DateTime(date('Y-m-d'));        
        $consulta->bindValue(':mail', $this->mail, PDO::PARAM_STR);
        $consulta->bindValue(':idTienda', $this->idTienda, PDO::PARAM_INT);
        $consulta->bindValue(':numeroPedido', $this->numeroPedido, PDO::PARAM_STR);
        $consulta->bindValue(':cantidad', $this->cantidad, PDO::PARAM_INT);
        $consulta->bindValue(':fechaAlta', date_format($fechaAlta,'Y-m-d'), PDO::PARAM_STR);      
        
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, mail, idTienda, numeroPedido, cantidad, fechaAlta FROM ventas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Venta');
    }

    public static function obtenerVentasPorUsuario($usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, mail, idTienda, numeroPedido, cantidad, fechaAlta FROM ventas WHERE mail = :usuario");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Venta');
    }
    public static function obtenerVentasPorNumeroPedido($numeroPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, mail, idTienda, numeroPedido, cantidad, fechaAlta FROM ventas WHERE numeroPedido = :numeroPedido");
        $consulta->bindValue(':numeroPedido', $numeroPedido, PDO::PARAM_STR);        
        
        $consulta->execute();

        return $consulta->fetchObject('Venta');
    }  
    public static function obtenerProductosVendidosPorFecha($fecha)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT SUM(cantidad) AS CANTIDAD_VENDIDA FROM ventas WHERE fechaAlta = :fechaAlta");
        $consulta->bindValue(':fechaAlta', $fecha, PDO::PARAM_STR);        
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_NUM);
    }
    public static function obtenerVentasPorTipo($tipo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT ventas.id, ventas.mail, ventas.idTienda, ventas.numeroPedido, ventas.cantidad, ventas.fechaAlta FROM ventas, tienda 
        WHERE tienda.tipo = :tipo AND ventas.idTienda = tienda.id");
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);        
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Venta');
    } 
    public static function obtenerVentasOrdenadasPorAnioSalida()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT ventas.id, ventas.mail, ventas.idTienda, ventas.numeroPedido, ventas.cantidad, ventas.fechaAlta FROM ventas, tienda 
        WHERE ventas.idTienda = tienda.id ORDER BY tienda.anioSalida DESC");           
        
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Venta');
    }

    public function modificarVenta()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE ventas SET mail = :mail, idTienda = :idTienda, cantidad = :cantidad, fechaAlta = :fechaAlta WHERE numeroPedido = :numeroPedido");
        $consulta->bindValue(':mail', $this->mail, PDO::PARAM_STR);
        $consulta->bindValue(':idTienda', $this->idTienda, PDO::PARAM_INT);
        $consulta->bindValue(':fechaAlta', $this->fechaAlta, PDO::PARAM_STR);
        $consulta->bindValue(':cantidad', $this->cantidad, PDO::PARAM_INT);
        $consulta->bindValue(':numeroPedido', $this->numeroPedido, PDO::PARAM_STR);
                
        $consulta->execute();
    }

    public function borrarVenta()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE ventas WHERE id = :id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
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