import bcrypt
from flask import Blueprint, render_template, request, session, redirect, url_for, flash, jsonify
from database import get_db
from models.usuario import toggle_estado_usuario
from models.producto import actualizar_stock_producto

admin_bp = Blueprint('admin', __name__)

@admin_bp.before_request
def check_admin():
    if 'id_usuario' not in session:
        return redirect(url_for('auth.login'))
    # Solo Administrador es rol activo para administración
    if session.get('rol_name') != 'Administrador':
        flash("Acceso denegado. Se requieren permisos de administrador.", "error")
        return redirect(url_for('client.dashboard'))

@admin_bp.route('/admin/dashboard')
def dashboard():
    search_query = request.args.get('search_client', '').strip()
    
    conn = get_db()
    cur = conn.cursor()
    try:
        # 1. Obtener todos los usuarios
        cur.execute("""
            SELECT u.id_usuario, u.username, u.estado, p.nombre_completo, r.nombre_rol, p.telefono, p.direccion, u.id_rol, p.ci_nit
            FROM usuarios u
            JOIN personas p ON u.id_persona = p.id_persona
            JOIN roles r ON u.id_rol = r.id_rol
            ORDER BY u.id_usuario;
        """)
        usuarios = cur.fetchall()
        
        # 2. Obtener clientes registrados
        if search_query:
            cur.execute("""
                SELECT c.id_cliente, p.nombre_completo, p.ci_nit, p.telefono, p.direccion, c.fecha_registro, p.id_persona
                FROM clientes c
                JOIN personas p ON c.id_persona = p.id_persona
                WHERE p.ci_nit LIKE %s OR p.telefono LIKE %s OR p.nombre_completo ILIKE %s
                ORDER BY p.nombre_completo;
            """, (f"%{search_query}%", f"%{search_query}%", f"%{search_query}%"))
        else:
            cur.execute("""
                SELECT c.id_cliente, p.nombre_completo, p.ci_nit, p.telefono, p.direccion, c.fecha_registro, p.id_persona
                FROM clientes c
                JOIN personas p ON c.id_persona = p.id_persona
                ORDER BY p.nombre_completo;
            """)
        clientes = cur.fetchall()
        
        # 3. Obtener inventario de productos
        cur.execute("""
            SELECT p.id_producto, p.codigo_barras, p.nombre_producto, p.precio_venta, p.stock_actual, p.stock_minimo, cat.nombre_categoria, p.id_categoria
            FROM productos p
            JOIN categorias cat ON p.id_categoria = cat.id_categoria
            ORDER BY p.id_producto;
        """)
        inventario = cur.fetchall()
        
        # 4. Obtener categorías para selectores
        cur.execute("SELECT id_categoria, nombre_categoria FROM categorias ORDER BY nombre_categoria;")
        categorias = cur.fetchall()
        
    finally:
        cur.close()
        
    return render_template('dashboard/panel_admin.php', 
                           usuarios=usuarios, 
                           clientes=clientes, 
                           inventario=inventario, 
                           categorias=categorias,
                           search_query=search_query)

# ==========================================
# MÓDULO INVENTARIO: CREAR, EDITAR, ELIMINAR
# ==========================================

@admin_bp.route('/admin/inventory/create', methods=['POST'])
def create_product():
    codigo_barras = request.form.get('codigo_barras', '').strip()
    nombre_producto = request.form.get('nombre_producto', '').strip()
    precio_venta = float(request.form.get('precio_venta', 0))
    stock_actual = int(request.form.get('stock_actual', 0))
    stock_minimo = int(request.form.get('stock_minimo', 5))
    id_categoria = int(request.form.get('id_categoria'))
    
    if not (codigo_barras and nombre_producto and id_categoria):
        flash("Todos los campos para añadir producto son obligatorios.", "error")
        return redirect(url_for('admin.dashboard', active_tab='inventario'))
        
    conn = get_db()
    cur = conn.cursor()
    try:
        cur.execute("""
            INSERT INTO productos (codigo_barras, nombre_producto, precio_venta, stock_actual, stock_minimo, id_categoria)
            VALUES (%s, %s, %s, %s, %s, %s);
        """, (codigo_barras, nombre_producto, precio_venta, stock_actual, stock_minimo, id_categoria))
        conn.commit()
        flash(f"Material '{nombre_producto}' añadido al inventario.", "success")
    except Exception as e:
        conn.rollback()
        flash(f"Error al añadir producto: {str(e)}", "error")
    finally:
        cur.close()
        
    return redirect(url_for('admin.dashboard', active_tab='inventario'))

@admin_bp.route('/admin/inventory/edit/<int:id_producto>', methods=['POST'])
def edit_product(id_producto):
    codigo_barras = request.form.get('codigo_barras', '').strip()
    nombre_producto = request.form.get('nombre_producto', '').strip()
    precio_venta = float(request.form.get('precio_venta', 0))
    stock_actual = int(request.form.get('stock_actual', 0))
    stock_minimo = int(request.form.get('stock_minimo', 5))
    id_categoria = int(request.form.get('id_categoria'))
    
    if not (codigo_barras and nombre_producto):
        flash("El código de barras y el nombre del material son obligatorios.", "error")
        return redirect(url_for('admin.dashboard', active_tab='inventario'))
        
    conn = get_db()
    cur = conn.cursor()
    try:
        cur.execute("""
            UPDATE productos 
            SET codigo_barras = %s, nombre_producto = %s, precio_venta = %s, stock_actual = %s, stock_minimo = %s, id_categoria = %s
            WHERE id_producto = %s;
        """, (codigo_barras, nombre_producto, precio_venta, stock_actual, stock_minimo, id_categoria, id_producto))
        conn.commit()
        flash(f"Material '{nombre_producto}' editado con éxito.", "success")
    except Exception as e:
        conn.rollback()
        flash(f"Error al editar producto: {str(e)}", "error")
    finally:
        cur.close()
        
    return redirect(url_for('admin.dashboard', active_tab='inventario'))

@admin_bp.route('/admin/inventory/delete/<int:id_producto>')
def delete_product(id_producto):
    conn = get_db()
    cur = conn.cursor()
    try:
        cur.execute("SELECT nombre_producto FROM productos WHERE id_producto = %s;", (id_producto,))
        prod = cur.fetchone()
        if not prod:
            flash("Producto no encontrado.", "error")
            return redirect(url_for('admin.dashboard', active_tab='inventario'))
            
        cur.execute("DELETE FROM productos WHERE id_producto = %s;", (id_producto,))
        conn.commit()
        flash(f"Material '{prod[0]}' eliminado del catálogo.", "success")
    except Exception as e:
        conn.rollback()
        flash(f"Error al eliminar producto: {str(e)}", "error")
    finally:
        cur.close()
        
    return redirect(url_for('admin.dashboard', active_tab='inventario'))

# ==========================================
# MÓDULO CLIENTES: CREAR, EDITAR, ELIMINAR
# ==========================================

@admin_bp.route('/admin/clients/create', methods=['POST'])
def create_client():
    """Registrar un cliente directamente en el panel de control."""
    username = request.form.get('username', '').strip()
    password = request.form.get('password', '')
    ci_nit = request.form.get('ci_nit', '').strip()
    nombre_completo = request.form.get('nombre_completo', '').strip()
    telefono = request.form.get('telefono', '').strip()
    direccion = request.form.get('direccion', '').strip()
    
    if not (username and password and ci_nit and nombre_completo and telefono and direccion):
        flash("Todos los campos para el registro de cliente son obligatorios.", "error")
        return redirect(url_for('admin.dashboard', active_tab='crear-cliente'))
        
    conn = get_db()
    cur = conn.cursor()
    try:
        # Verificar duplicados
        cur.execute("SELECT 1 FROM usuarios WHERE username = %s;", (username,))
        if cur.fetchone():
            flash("El nombre de usuario ya existe.", "error")
            return redirect(url_for('admin.dashboard', active_tab='crear-cliente'))
            
        cur.execute("SELECT 1 FROM personas WHERE ci_nit = %s;", (ci_nit,))
        if cur.fetchone():
            flash("El CI ya está registrado.", "error")
            return redirect(url_for('admin.dashboard', active_tab='crear-cliente'))
            
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
            INSERT INTO clientes (id_persona) VALUES (%s);
        """, (id_persona,))
        
        conn.commit()
        flash(f"Cliente '{nombre_completo}' registrado correctamente.", "success")
        
    except Exception as e:
        conn.rollback()
        flash(f"Error al registrar cliente: {str(e)}", "error")
    finally:
        cur.close()
        
    return redirect(url_for('admin.dashboard', active_tab='crear-cliente'))

@admin_bp.route('/admin/clients/edit/<int:id_cliente>', methods=['POST'])
def edit_client(id_cliente):
    nombre_completo = request.form.get('nombre_completo', '').strip()
    ci_nit = request.form.get('ci_nit', '').strip()
    telefono = request.form.get('telefono', '').strip()
    direccion = request.form.get('direccion', '').strip()
    
    if not (nombre_completo and ci_nit):
        flash("Nombre y CI son campos requeridos.", "error")
        return redirect(url_for('admin.dashboard', active_tab='clientes'))
        
    conn = get_db()
    cur = conn.cursor()
    try:
        # Obtener id_persona de este cliente
        cur.execute("SELECT id_persona FROM clientes WHERE id_cliente = %s;", (id_cliente,))
        res = cur.fetchone()
        if not res:
            flash("Cliente no encontrado.", "error")
            return redirect(url_for('admin.dashboard', active_tab='clientes'))
            
        id_persona = res[0]
        
        # Actualizar datos de la persona
        cur.execute("""
            UPDATE personas 
            SET nombre_completo = %s, ci_nit = %s, telefono = %s, direccion = %s
            WHERE id_persona = %s;
        """, (nombre_completo, ci_nit, telefono, direccion, id_persona))
        conn.commit()
        flash("Datos del cliente editados con éxito.", "success")
    except Exception as e:
        conn.rollback()
        flash(f"Error al editar cliente: {str(e)}", "error")
    finally:
        cur.close()
        
    return redirect(url_for('admin.dashboard', active_tab='clientes'))

@admin_bp.route('/admin/clients/delete/<int:id_cliente>')
def delete_client(id_cliente):
    conn = get_db()
    cur = conn.cursor()
    try:
        cur.execute("SELECT p.nombre_completo, c.id_persona FROM clientes c JOIN personas p ON c.id_persona = p.id_persona WHERE c.id_cliente = %s;", (id_cliente,))
        res = cur.fetchone()
        if not res:
            flash("Cliente no encontrado.", "error")
            return redirect(url_for('admin.dashboard', active_tab='clientes'))
            
        nombre, id_persona = res
        
        # Al eliminar la persona en cascada, PostgreSQL elimina su usuario y cliente asociados
        cur.execute("DELETE FROM personas WHERE id_persona = %s;", (id_persona,))
        conn.commit()
        flash(f"Cliente '{nombre}' y su cuenta asociada han sido eliminados.", "success")
    except Exception as e:
        conn.rollback()
        flash(f"Error al eliminar cliente: {str(e)}", "error")
    finally:
        cur.close()
        
    return redirect(url_for('admin.dashboard', active_tab='clientes'))

# ==========================================
# MÓDULO USUARIOS: EDITAR, ELIMINAR, TOGGLE
# ==========================================

@admin_bp.route('/admin/users/toggle/<int:id_usuario>')
def toggle_user(id_usuario):
    if id_usuario == session.get('id_usuario'):
        flash("No puedes desactivar tu propia cuenta activa de administrador.", "error")
        return redirect(url_for('admin.dashboard', active_tab='usuarios'))
        
    conn = get_db()
    cur = conn.cursor()
    try:
        cur.execute("SELECT estado, username FROM usuarios WHERE id_usuario = %s;", (id_usuario,))
        user = cur.fetchone()
        if not user:
            flash("Usuario no encontrado.", "error")
            return redirect(url_for('admin.dashboard', active_tab='usuarios'))
            
        new_state = toggle_estado_usuario(cur, id_usuario, user[0])
        conn.commit()
        
        estado_txt = "habilitado" if new_state else "deshabilitado"
        flash(f"El usuario '{user[1]}' ha sido {estado_txt} correctamente.", "success")
    except Exception as e:
        conn.rollback()
        flash(f"Error al cambiar estado del usuario: {str(e)}", "error")
    finally:
        cur.close()
        
    return redirect(url_for('admin.dashboard', active_tab='usuarios'))

@admin_bp.route('/admin/users/delete/<int:id_usuario>')
def delete_user(id_usuario):
    if id_usuario == session.get('id_usuario'):
        flash("No puedes eliminar tu propia cuenta de administrador en sesión.", "error")
        return redirect(url_for('admin.dashboard', active_tab='usuarios'))
        
    conn = get_db()
    cur = conn.cursor()
    try:
        cur.execute("SELECT username, id_persona FROM usuarios WHERE id_usuario = %s;", (id_usuario,))
        res = cur.fetchone()
        if not res:
            flash("Usuario no encontrado.", "error")
            return redirect(url_for('admin.dashboard', active_tab='usuarios'))
            
        username, id_persona = res
        
        # Eliminar persona en cascada
        cur.execute("DELETE FROM personas WHERE id_persona = %s;", (id_persona,))
        conn.commit()
        flash(f"Usuario '{username}' eliminado correctamente.", "success")
    except Exception as e:
        conn.rollback()
        flash(f"Error al eliminar usuario: {str(e)}", "error")
    finally:
        cur.close()
        
    return redirect(url_for('admin.dashboard', active_tab='usuarios'))

@admin_bp.route('/admin/users/edit/<int:id_usuario>', methods=['POST'])
def edit_user(id_usuario):
    username = request.form.get('username', '').strip()
    nombre_completo = request.form.get('nombre_completo', '').strip()
    ci_nit = request.form.get('ci_nit', '').strip()
    telefono = request.form.get('telefono', '').strip()
    direccion = request.form.get('direccion', '').strip()
    id_rol = int(request.form.get('id_rol'))
    password = request.form.get('password', '')
    
    if not (username and nombre_completo and ci_nit and id_rol):
        flash("Campos obligatorios para editar usuario incompletos.", "error")
        return redirect(url_for('admin.dashboard', active_tab='usuarios'))
        
    conn = get_db()
    cur = conn.cursor()
    try:
        cur.execute("SELECT id_persona, id_rol FROM usuarios WHERE id_usuario = %s;", (id_usuario,))
        res = cur.fetchone()
        if not res:
            flash("Usuario no encontrado.", "error")
            return redirect(url_for('admin.dashboard', active_tab='usuarios'))
            
        id_persona, old_rol = res
        
        # Actualizar persona
        cur.execute("""
            UPDATE personas 
            SET nombre_completo = %s, ci_nit = %s, telefono = %s, direccion = %s
            WHERE id_persona = %s;
        """, (nombre_completo, ci_nit, telefono, direccion, id_persona))
        
        # Actualizar usuario
        if password:
            password_hash = bcrypt.hashpw(password.encode('utf-8'), bcrypt.gensalt()).decode('utf-8')
            cur.execute("""
                UPDATE usuarios 
                SET username = %s, id_rol = %s, password_hash = %s
                WHERE id_usuario = %s;
            """, (username, id_rol, password_hash, id_usuario))
        else:
            cur.execute("""
                UPDATE usuarios 
                SET username = %s, id_rol = %s
                WHERE id_usuario = %s;
            """, (username, id_rol, id_usuario))
            
        # Sincronizar clientes
        if id_rol == 2 and old_rol != 2:
            cur.execute("SELECT 1 FROM clientes WHERE id_persona = %s;", (id_persona,))
            if not cur.fetchone():
                cur.execute("INSERT INTO clientes (id_persona) VALUES (%s);", (id_persona,))
        elif id_rol != 2 and old_rol == 2:
            cur.execute("DELETE FROM clientes WHERE id_persona = %s;", (id_persona,))
            
        conn.commit()
        flash("Cuenta de acceso y datos personales actualizados.", "success")
    except Exception as e:
        conn.rollback()
        flash(f"Error al editar usuario: {str(e)}", "error")
    finally:
        cur.close()
        
    return redirect(url_for('admin.dashboard', active_tab='usuarios'))

@admin_bp.route('/admin/inventory/update_stock', methods=['POST'])
def update_stock():
    id_prod = request.form.get('id_producto')
    new_stock = request.form.get('stock')
    
    if not id_prod or new_stock is None:
        flash("Datos del producto incompletos.", "error")
        return redirect(url_for('admin.dashboard', active_tab='inventario'))
        
    try:
        new_stock = int(new_stock)
        if new_stock < 0:
            flash("El stock no puede ser negativo.", "error")
            return redirect(url_for('admin.dashboard', active_tab='inventario'))
    except ValueError:
        flash("El stock debe ser un número entero válido.", "error")
        return redirect(url_for('admin.dashboard', active_tab='inventario'))
        
    conn = get_db()
    cur = conn.cursor()
    try:
        actualizar_stock_producto(conn, cur, id_prod, new_stock)
        flash("Inventario de stock actualizado.", "success")
    except Exception as e:
        conn.rollback()
        flash(f"Error al actualizar: {str(e)}", "error")
    finally:
        cur.close()
        
    return redirect(url_for('admin.dashboard', active_tab='inventario'))
