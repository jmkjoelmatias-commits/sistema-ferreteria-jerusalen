<?php
/**
 * Modelo de Transacciones y Cotizaciones en PHP
 */

class VentaModel {
    
    public static function guardarCotizacion($db, $id_cliente, $tipo_cotizacion, $precio_total, $detalleJson) {
        $query = "INSERT INTO cotizaciones_guardadas (id_cliente, tipo_cotizacion, precio_total, detalle)
                  VALUES ($1, $2, $3, $4) RETURNING id_cotizacion";
        $result = pg_query_params($db, $query, [$id_cliente, $tipo_cotizacion, $precio_total, $detalleJson]);
        if ($result) {
            return pg_fetch_result($result, 0, 0);
        }
        return false;
    }

    public static function getHistorial($db, $id_cliente) {
        $query = "SELECT id_cotizacion, tipo_cotizacion, precio_total, fecha_hora
                  FROM cotizaciones_guardadas
                  WHERE id_cliente = $1
                  ORDER BY fecha_hora DESC";
        $result = pg_query_params($db, $query, [$id_cliente]);
        return pg_fetch_all($result);
    }
}
?>
