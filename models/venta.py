import json

def guardar_cotizacion(conn, cur, id_cliente, tipo_cotizacion, precio_total, detalle):
    """Guarda una cotización (tradicional o IA) en cotizaciones_guardadas."""
    cur.execute("""
        INSERT INTO cotizaciones_guardadas (id_cliente, tipo_cotizacion, precio_total, detalle)
        VALUES (%s, %s, %s, %s) RETURNING id_cotizacion;
    """, (id_cliente, tipo_cotizacion, precio_total, json.dumps(detalle)))
    conn.commit()
    return cur.fetchone()[0]

def get_cotizacion_por_id(cur, id_cotizacion, id_cliente=None):
    """Retorna los detalles de una cotización opcionalmente filtrada por cliente."""
    if id_cliente:
        cur.execute("""
            SELECT id_cotizacion, tipo_cotizacion, precio_total, fecha_hora, detalle
            FROM cotizaciones_guardadas
            WHERE id_cotizacion = %s AND id_cliente = %s;
        """, (id_cotizacion, id_cliente))
    else:
        cur.execute("""
            SELECT id_cotizacion, tipo_cotizacion, precio_total, fecha_hora, detalle
            FROM cotizaciones_guardadas
            WHERE id_cotizacion = %s;
        """, (id_cotizacion,))
    return cur.fetchone()

def get_historial_cotizaciones_cliente(cur, id_cliente):
    """Retorna todas las cotizaciones de un cliente ordenadas por fecha."""
    cur.execute("""
        SELECT id_cotizacion, tipo_cotizacion, precio_total, fecha_hora
        FROM cotizaciones_guardadas
        WHERE id_cliente = %s
        ORDER BY fecha_hora DESC;
    """, (id_cliente,))
    return cur.fetchall()
