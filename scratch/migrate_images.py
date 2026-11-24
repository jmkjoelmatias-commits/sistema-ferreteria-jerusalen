import sys, os
sys.path.insert(0, os.path.abspath('.'))
from app import create_app
from database import get_db

app = create_app()
with app.app_context():
    conn = get_db()
    cur = conn.cursor()
    
    # 1. Agregar columna imagen si no existe
    cur.execute("""
        ALTER TABLE productos ADD COLUMN IF NOT EXISTS imagen VARCHAR(255);
    """)
    conn.commit()
    print("Columna 'imagen' verificada/agregada a la tabla productos.")
    
    # 2. Actualizar productos existentes con sus fotos
    updates = [
        ('cemento_canva.png', 'Cemento Viacha IP-40 (50kg)'),
        ('fierro_arequipa.jpg', 'Fierro Corrugado 12mm (Arequipa)'),
        ('Ladrillo_de_18_huecos_gambote_ceramitex.jpg', 'Ladrillo Cerámico Gambote 18 Huecos')
    ]
    for img, name in updates:
        cur.execute("UPDATE productos SET imagen = %s WHERE nombre_producto ILIKE %s;", (img, f"%{name}%"))
    conn.commit()
    print("Productos existentes actualizados con sus fotos.")
    
    # 3. Insertar los nuevos materiales de fotos_materiales si no existen
    nuevos_materiales = [
        ('7401009', 'Alambre de Amarre Arequipa (1kg)', 18.00, 150, 20, 1, 'Alambre_de_amarre_Arequipa.jpg'),
        ('7401010', 'Casetones de Poliestireno Plastofor', 28.00, 80, 10, 1, 'casetones_de_poliestireno.jpg'),
        ('7401011', 'Cemento Ecebol IP-30 (50kg)', 48.00, 160, 25, 1, 'cemento_ecebol.jpg'),
        ('7401012', 'Cemento Eco Plus Especial (50kg)', 46.50, 120, 20, 1, 'cemento_eco_plus.png'),
        ('7401013', 'Clavo de Construcción CONSTRUCTOR (1kg)', 14.00, 200, 30, 1, 'clavo_CONSTRUCTOR.jpg'),
        ('7401014', 'Clavo PRAMEC Punta París (1kg)', 15.50, 180, 25, 1, 'Clavo_PRAMEC.jpg'),
        ('7401015', 'Fierro Corrugado Ecebol 10mm', 68.00, 95, 15, 1, 'fierro_ecebol.jpg'),
        ('7401016', 'Fierro Corrugado Las Lomas 12mm', 84.00, 110, 20, 1, 'fierro_las_lomas.jpg'),
        ('7401017', 'Ladrillo 6 Huecos La Cerámica', 1.95, 800, 150, 1, 'Ladrillo_de_6_huecos_La_ceramica.jpg'),
        ('7401018', 'Viguetas Pretensadas PRETEC Ltda.', 45.00, 75, 10, 1, 'Viguetas_pretendas_PRETEC_LIMITADA_SRL.jpg'),
    ]
    
    for cod, nom, precio, stock, stock_min, cat_id, img in nuevos_materiales:
        cur.execute("SELECT id_producto FROM productos WHERE codigo_barras = %s OR nombre_producto = %s;", (cod, nom))
        if not cur.fetchone():
            cur.execute("""
                INSERT INTO productos (codigo_barras, nombre_producto, precio_venta, stock_actual, stock_minimo, id_categoria, imagen)
                VALUES (%s, %s, %s, %s, %s, %s, %s);
            """, (cod, nom, precio, stock, stock_min, cat_id, img))
            print(f"Insertado material: {nom}")
        else:
            cur.execute("UPDATE productos SET imagen = %s WHERE codigo_barras = %s OR nombre_producto = %s;", (img, cod, nom))
            print(f"Actualizado material: {nom}")
            
    conn.commit()
    
    # 4. Verificar todos los productos
    cur.execute("SELECT id_producto, codigo_barras, nombre_producto, precio_venta, stock_actual, imagen FROM productos ORDER BY id_producto;")
    print("\nLista actual de materiales con fotos:")
    for row in cur.fetchall():
        print(row)
        
    cur.close()
