import bcrypt

def get_usuario_por_username(cur, username):
    """Busca un usuario por su username e incluye su rol."""
    cur.execute("""
        SELECT u.id_usuario, u.username, u.password_hash, r.nombre_rol, u.estado, u.id_persona
        FROM usuarios u
        JOIN roles r ON u.id_rol = r.id_rol
        WHERE u.username = %s;
    """, (username,))
    return cur.fetchone()

def toggle_estado_usuario(cur, id_usuario, current_state):
    """Activa/Desactiva una cuenta de usuario."""
    new_state = not current_state
    cur.execute("""
        UPDATE usuarios 
        SET estado = %s 
        WHERE id_usuario = %s;
    """, (new_state, id_usuario))
    return new_state

def crear_cliente_relacional(conn, cur, ci_nit, nombre_completo, telefono, direccion, username, password):
    """Crea una persona, su cuenta de usuario con rol Cliente, y su registro como cliente."""
    # 1. Crear Persona
    cur.execute("""
        INSERT INTO personas (ci_nit, nombre_completo, telefono, direccion)
        VALUES (%s, %s, %s, %s) RETURNING id_persona;
    """, (ci_nit, nombre_completo, telefono, direccion))
    id_persona = cur.fetchone()[0]
    
    # 2. Crear Usuario (Rol 2 = Cliente)
    password_hash = bcrypt.hashpw(password.encode('utf-8'), bcrypt.gensalt()).decode('utf-8')
    cur.execute("""
        INSERT INTO usuarios (id_persona, username, password_hash, id_rol)
        VALUES (%s, %s, %s, 2);
    """, (id_persona, username, password_hash))
    
    # 3. Crear Cliente
    cur.execute("""
        INSERT INTO clientes (id_persona) VALUES (%s) RETURNING id_cliente;
    """, (id_persona,))
    id_cliente = cur.fetchone()[0]
    
    conn.commit()
    return id_persona, id_cliente

def crear_usuario_generico(conn, cur, ci_nit, nombre_completo, telefono, direccion, username, password, id_rol):
    """Crea una persona y su respectiva credencial de usuario con cualquier rol."""
    # 1. Crear Persona
    cur.execute("""
        INSERT INTO personas (ci_nit, nombre_completo, telefono, direccion)
        VALUES (%s, %s, %s, %s) RETURNING id_persona;
    """, (ci_nit, nombre_completo, telefono, direccion))
    id_persona = cur.fetchone()[0]
    
    # 2. Crear Usuario
    password_hash = bcrypt.hashpw(password.encode('utf-8'), bcrypt.gensalt()).decode('utf-8')
    cur.execute("""
        INSERT INTO usuarios (id_persona, username, password_hash, id_rol)
        VALUES (%s, %s, %s, %s) RETURNING id_usuario;
    """, (id_persona, username, password_hash, id_rol))
    id_usuario = cur.fetchone()[0]
    
    # 3. Si el rol es Cliente (id_rol == 2), también se inserta en clientes
    id_cliente = None
    if int(id_rol) == 2:
        cur.execute("""
            INSERT INTO clientes (id_persona) VALUES (%s) RETURNING id_cliente;
        """, (id_persona,))
        id_cliente = cur.fetchone()[0]
        
    conn.commit()
    return id_persona, id_usuario, id_cliente
