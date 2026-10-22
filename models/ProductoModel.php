<?php
/**
 * Modelo de Producto en PHP - Catálogos e inventario con PostgreSQL
 */

class ProductoModel {
    
    public static function getAll($db) {
        $query = "SELECT p.id_producto, p.codigo_barras, p.nombre_producto, p.precio_venta, p.stock_actual, p.stock_minimo, c.nombre_categoria
                  FROM productos p
                  JOIN categorias c ON p.id_categoria = c.id_categoria
                  ORDER BY p.nombre_producto";
        $result = pg_query($db, $query);
        return pg_fetch_all($result);
    }

    public static function updateStock($db, $id_producto, $newStock) {
        $query = "UPDATE productos SET stock_actual = $1 WHERE id_producto = $2";
        $result = pg_query_params($db, $query, [$newStock, $id_producto]);
        return (bool)$result;
    }
}
?>
