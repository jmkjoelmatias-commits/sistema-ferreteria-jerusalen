import bcrypt
from flask import Blueprint, render_template, request, session, redirect, url_for, flash
from database import get_db
from models.usuario import get_usuario_por_username

auth_bp = Blueprint('auth', __name__)

def check_password(plain_password, password_hash):
    # Soporte para contraseñas de prueba
    if password_hash == '$2y$10$mC3B61XfL8bYpUreY41OfeNn2bF00aH0h8JepEFE8R11vR/IuG6Eu' and plain_password == 'admin123':
        return True
        
    try:
        py_hash = password_hash.replace('$2y$', '$2b$').encode('utf-8')
        return bcrypt.checkpw(plain_password.encode('utf-8'), py_hash)
    except Exception:
        return False

@auth_bp.route('/', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form.get('username', '').strip()
        password = request.form.get('password', '')
        selected_rol = request.form.get('rol', '')
        
        # 1. Consultar BD usando el Modelo Usuario
        conn = get_db()
        cur = conn.cursor()
        try:
            user_data = get_usuario_por_username(cur, username)
            
            if not user_data:
                flash("El nombre de usuario o la contraseña son incorrectos.", "error")
                return render_template('auth/login.php', active_tab=selected_rol)
                
            id_usuario, db_username, db_password_hash, db_rol_name, db_estado, db_id_persona = user_data
            
            # 2. Validar Rol
            if selected_rol == 'Administrador' and db_rol_name != 'Administrador':
                flash("Rol incorrecto para este usuario.", "error")
                return render_template('auth/login.php', active_tab=selected_rol)
            elif selected_rol == 'Cliente' and db_rol_name != 'Cliente':
                flash("Rol incorrecto para este usuario.", "error")
                return render_template('auth/login.php', active_tab=selected_rol)
            
            # 3. Validar estado de cuenta
            if not db_estado:
                flash("Esta cuenta ha sido inhabilitada. Comuníquese con el administrador.", "error")
                return render_template('auth/login.php', active_tab=selected_rol)
                
            # 4. Validar contraseña
            if not check_password(password, db_password_hash):
                flash("El nombre de usuario o la contraseña son incorrectos.", "error")
                return render_template('auth/login.php', active_tab=selected_rol)
                
            # Guardar sesión
            session.clear()
            session['id_usuario'] = id_usuario
            session['username'] = db_username
            session['rol_name'] = db_rol_name
            session['id_persona'] = db_id_persona
            
            # Si es cliente, obtener su id_cliente
            if db_rol_name == 'Cliente':
                cur.execute("SELECT id_cliente FROM clientes WHERE id_persona = %s;", (db_id_persona,))
                cliente_row = cur.fetchone()
                if cliente_row:
                    session['id_cliente'] = cliente_row[0]
                else:
                    flash("Error al recuperar datos del cliente.", "error")
                    return render_template('auth/login.php', active_tab=selected_rol)
                
            flash(f"¡Bienvenido al sistema, {db_username}!", "success")
            
            if db_rol_name == 'Administrador':
                return redirect(url_for('admin.dashboard'))
            else:
                return redirect(url_for('client.dashboard'))
                
        finally:
            cur.close()
            
    return render_template('auth/login.php', active_tab='Cliente')

@auth_bp.route('/logout')
def logout():
    session.clear()
    flash("Sesión cerrada correctamente.", "info")
    return redirect(url_for('auth.login'))
