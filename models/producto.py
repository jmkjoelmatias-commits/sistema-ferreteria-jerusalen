def get_todos_productos(cur):
    """Retorna todos los productos con el nombre de su categoría."""
    cur.execute("""
        SELECT p.id_producto, p.codigo_barras, p.nombre_producto, p.precio_venta, p.stock_actual, p.stock_minimo, c.nombre_categoria
        FROM productos p
        JOIN categorias c ON p.id_categoria = c.id_categoria
        ORDER BY p.nombre_producto;
    """)
    return cur.fetchall()

def actualizar_stock_producto(conn, cur, id_producto, new_stock):
    """Actualiza la cantidad de stock actual de un producto."""
    cur.execute("""
        UPDATE productos
        SET stock_actual = %s
        WHERE id_producto = %s;
    """, (new_stock, id_producto))
    conn.commit()
