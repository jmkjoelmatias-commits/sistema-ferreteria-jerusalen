import os
from flask import Flask
from config import Config
from database import close_db, init_db, get_db

def create_app():
    # Inicializar Flask con template_folder apuntando a la carpeta de vistas y static_folder a public
    app = Flask(
        __name__, 
        template_folder='views',
        static_folder='public',
        static_url_path='/public'
    )
    app.config.from_object(Config)

    # Registrar cierre de base de datos
    app.teardown_appcontext(close_db)

    # Registrar blueprints
    from controllers.auth import auth_bp
    from controllers.client import client_bp
    from controllers.admin import admin_bp

    app.register_blueprint(auth_bp)
    app.register_blueprint(client_bp)
    app.register_blueprint(admin_bp)

    # Inicialización automática de la base de datos si falta alguna tabla crítica
    with app.app_context():
        try:
            conn = get_db()
            cur = conn.cursor()
            cur.execute("SELECT 1 FROM information_schema.tables WHERE table_name = 'usuarios' AND table_schema = 'public';")
            exists = cur.fetchone()
            cur.close()
            
            if not exists:
                print("Tabla 'usuarios' no encontrada. Inicializando base de datos...")
                init_db()
            else:
                print("Base de datos conectada correctamente y tablas verificadas.")
        except Exception as e:
            print("Error al verificar la base de datos, intentando inicializar...", e)
            try:
                init_db()
            except Exception as ex:
                print("Error crítico al inicializar la base de datos. Verifica tus credenciales en config.py.", ex)

    return app

if __name__ == '__main__':
    app = create_app()
    # Levantar puerto web local 8000
    app.run(host='0.0.0.0', port=8000, debug=True)
