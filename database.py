import psycopg2
from flask import current_app, g

def get_db():
    if 'db' not in g:
        try:
            g.db = psycopg2.connect(
                host=current_app.config['DB_HOST'],
                port=current_app.config['DB_PORT'],
                dbname=current_app.config['DB_NAME'],
                user=current_app.config['DB_USER'],
                password=current_app.config['DB_PASS']
            )
        except UnicodeDecodeError:
            raise Exception("ERROR DE CONEXIÓN: Verifica que la contraseña, el usuario y el nombre de la base de datos en config.py sean los correctos.")
    return g.db

def close_db(e=None):
    db = g.pop('db', None)
    if db is not None:
        db.close()

def init_db():
    """Inicializa la base de datos recreando las tablas con dos roles ('Administrador', 'Cliente') y datos semilla."""
    conn = psycopg2.connect(
        host=current_app.config['DB_HOST'],
        port=current_app.config['DB_PORT'],
        dbname=current_app.config['DB_NAME'],
        user=current_app.config['DB_USER'],
        password=current_app.config['DB_PASS']
    )
    cur = conn.cursor()
    
    # 1. Limpieza de tablas existentes
    cur.execute("""
        DROP TABLE IF EXISTS cotizaciones_guardadas CASCADE;
        DROP TABLE IF EXISTS detalle_ventas CASCADE;
        DROP TABLE IF EXISTS ventas CASCADE;
        DROP TABLE IF EXISTS clientes CASCADE;
        DROP TABLE IF EXISTS usuarios CASCADE;
        DROP TABLE IF EXISTS roles CASCADE;
        DROP TABLE IF EXISTS productos CASCADE;
        DROP TABLE IF EXISTS categorias CASCADE;
        DROP TABLE IF EXISTS personas CASCADE;
    """)
    
    # 2. Entidades
    cur.execute("""
        CREATE TABLE personas (
            id_persona SERIAL PRIMARY KEY,
            ci_nit VARCHAR(20) NOT NULL UNIQUE,          
            nombre_completo VARCHAR(150) NOT NULL,      
            telefono VARCHAR(20),                       
            direccion VARCHAR(200)                      
        );
        
        CREATE TABLE roles (
            id_rol SERIAL PRIMARY KEY,
            nombre_rol VARCHAR(50) NOT NULL UNIQUE       
        );
        
        CREATE TABLE usuarios (
            id_usuario SERIAL PRIMARY KEY,
            id_persona INT NOT NULL UNIQUE,
            username VARCHAR(50) NOT NULL UNIQUE,        
            password_hash VARCHAR(255) NOT NULL,         
            id_rol INT NOT NULL,                         
            estado BOOLEAN DEFAULT TRUE,                 
            CONSTRAINT fk_usuario_persona FOREIGN KEY (id_persona) REFERENCES personas(id_persona) ON DELETE CASCADE,
            CONSTRAINT fk_usuario_rol FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
        );
        
        CREATE TABLE clientes (
            id_cliente SERIAL PRIMARY KEY,
            id_persona INT NOT NULL UNIQUE,              
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_cliente_persona FOREIGN KEY (id_persona) REFERENCES personas(id_persona) ON DELETE CASCADE
        );
        
        CREATE TABLE categorias (
            id_categoria SERIAL PRIMARY KEY,
            nombre_categoria VARCHAR(100) NOT NULL UNIQUE
        );
        
        CREATE TABLE productos (
            id_producto SERIAL PRIMARY KEY,
            codigo_barras VARCHAR(50) UNIQUE,            
            nombre_producto VARCHAR(150) NOT NULL,
            precio_venta NUMERIC(10,2) NOT NULL CHECK (precio_venta >= 0),
            stock_actual INT NOT NULL DEFAULT 0 CHECK (stock_actual >= 0),
            stock_minimo INT NOT NULL DEFAULT 5 CHECK (stock_minimo >= 0), 
            id_categoria INT NOT NULL,
            CONSTRAINT fk_producto_categoria FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria) ON DELETE CASCADE
        );
        
        CREATE TABLE ventas (
            id_venta SERIAL PRIMARY KEY,
            fecha_hora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            id_cliente INT NOT NULL,                     
            id_usuario INT NOT NULL,                     
            CONSTRAINT fk_venta_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE,
            CONSTRAINT fk_venta_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
        );
        
        CREATE TABLE detalle_ventas (
            id_detalle SERIAL PRIMARY KEY,
            id_venta INT NOT NULL,
            id_producto INT NOT NULL,
            cantidad INT NOT NULL CHECK (cantidad > 0),
            precio_historico NUMERIC(10,2) NOT NULL CHECK (precio_historico >= 0), 
            CONSTRAINT fk_detalle_venta FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE CASCADE,
            CONSTRAINT fk_detalle_producto FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE
        );
        
        CREATE TABLE cotizaciones_guardadas (
            id_cotizacion SERIAL PRIMARY KEY,
            id_cliente INT NOT NULL,
            tipo_cotizacion VARCHAR(20) NOT NULL CHECK (tipo_cotizacion IN ('Tradicional', 'IA')),
            precio_total NUMERIC(10,2) NOT NULL CHECK (precio_total >= 0),
            fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            detalle JSONB,
            CONSTRAINT fk_cotizacion_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE
        );
    """)
    
    # 3. Insertar roles (Solo Administrador y Cliente)
    cur.execute("""
        INSERT INTO roles (nombre_rol) VALUES ('Administrador'), ('Cliente');
    """)
    
    # 4. Insertar Joel Matias Ticona (Administrador, id_rol = 1)
    cur.execute("""
        INSERT INTO personas (ci_nit, nombre_completo, telefono, direccion) 
        VALUES ('9999999', 'Joel Matias Ticona', '77755533', 'El Alto, Av. Principal')
        RETURNING id_persona;
    """)
    id_persona_admin = cur.fetchone()[0]
    
    cur.execute("""
        INSERT INTO usuarios (id_persona, username, password_hash, id_rol) 
        VALUES (%s, 'joel_admin', '$2y$10$mC3B61XfL8bYpUreY41OfeNn2bF00aH0h8JepEFE8R11vR/IuG6Eu', 1);
    """, (id_persona_admin,))
    
    # 5. Insertar Carlos Mendoza Tarqui (Cliente, id_rol = 2)
    cur.execute("""
        INSERT INTO personas (ci_nit, nombre_completo, telefono, direccion) 
        VALUES ('1234567', 'Carlos Mendoza Tarqui', '65432100', 'Zona Ballivian, El Alto')
        RETURNING id_persona;
    """)
    id_persona_cliente = cur.fetchone()[0]
    
    import bcrypt
    pw_hash = bcrypt.hashpw(b'cliente123', bcrypt.gensalt()).decode('utf-8')
    cur.execute("""
        INSERT INTO usuarios (id_persona, username, password_hash, id_rol) 
        VALUES (%s, 'carlos_cliente', %s, 2);
    """, (id_persona_cliente, pw_hash))
    
    cur.execute("""
        INSERT INTO clientes (id_persona) VALUES (%s);
    """, (id_persona_cliente,))
    
    # 6. Categorías
    cur.execute("""
        INSERT INTO categorias (nombre_categoria) VALUES ('Obra Gruesa'), ('Herramientas'), ('Acabados');
    """)
    
    # 7. Productos
    productos = [
        ('7401001', 'Cemento Viacha IP-40 (50kg)', 52.00, 200, 20, 1),
        ('7401002', 'Fierro Corrugado 12mm (Arequipa)', 85.50, 90, 15, 1),
        ('7401003', 'Arena Fina (Cubo)', 120.00, 15, 5, 1),
        ('7401004', 'Combo de construccion de 4 Lbs Tramontina', 95.00, 12, 3, 2),
        ('7401005', 'Amoladora Angular Bosch 4 1/2 pulg', 480.00, 6, 2, 2),
        ('7401006', 'Mezcladora de Cemento Pro 150L', 3200.00, 3, 1, 1),
        ('7401007', 'Ladrillo Cerámico Gambote 18 Huecos', 1.80, 500, 100, 1),
        ('7401008', 'Plastificante Sika para Hormigón (1L)', 35.00, 4, 5, 3)
    ]
    for p in productos:
        cur.execute("""
            INSERT INTO productos (codigo_barras, nombre_producto, precio_venta, stock_actual, stock_minimo, id_categoria)
            VALUES (%s, %s, %s, %s, %s, %s);
        """, p)
        
    conn.commit()
    cur.close()
    conn.close()
    print("Base de datos inicializada con 2 roles correctamente.")
