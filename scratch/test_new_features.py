import sys, os
sys.path.insert(0, os.path.abspath('.'))
from app import create_app
from database import get_db

def test_all():
    print("========================================")
    print(" INICIANDO PRUEBAS DE NUEVAS FUNCIONES ")
    print("========================================")
    
    app = create_app()
    client = app.test_client()
    
    # 1. Comprobar que las imágenes existen en public/img/materiales
    materiales_path = os.path.join(app.static_folder, 'img', 'materiales')
    assert os.path.exists(materiales_path), "Carpeta public/img/materiales no existe"
    imgs = os.listdir(materiales_path)
    print(f"[OK] {len(imgs)} imágenes encontradas en public/img/materiales:")
    for img in sorted(imgs):
        print(f"   - {img}")
    
    assert 'default_material.svg' in imgs, "default_material.svg debe existir como fallback"
    
    # 2. Comprobar base de datos
    with app.app_context():
        cur = get_db().cursor()
        cur.execute("SELECT id_producto, nombre_producto, imagen FROM productos WHERE imagen IS NOT NULL;")
        prods_con_foto = cur.fetchall()
        print(f"\n[OK] {len(prods_con_foto)} productos configurados con imagen en BD:")
        for pid, name, img in prods_con_foto:
            print(f"   - ID {pid}: {name} -> {img}")
        assert len(prods_con_foto) >= 10, "Debe haber al menos 10 productos con imagen"

    # 3. Comprobar Login (Presencia de ojito para ver contraseña)
    res_login = client.get('/')
    assert res_login.status_code == 200
    html_login = res_login.data.decode('utf-8')
    assert 'togglePasswordVisibility' in html_login, "La vista de login debe tener función togglePasswordVisibility"
    assert 'password-input-wrapper' in html_login, "La vista de login debe tener wrapper para el ojito"
    assert 'toggle-password-btn' in html_login, "La vista de login debe tener botón de ojito"
    print("\n[OK] Formulario de Login contiene botón de ojito para mostrar/ocultar contraseña.")

    # 4. Probar Login como Cliente y verificar fotos en el catálogo
    with client.session_transaction() as sess:
        sess['id_usuario'] = 2
        sess['username'] = 'carlos_cliente'
        sess['rol_name'] = 'Cliente'
        sess['id_persona'] = 2
        sess['id_cliente'] = 1
        
    res_client = client.get('/client/dashboard')
    assert res_client.status_code == 200
    html_client = res_client.data.decode('utf-8')
    assert 'product-img-wrapper' in html_client, "El catálogo del cliente debe tener contenedores de fotos"
    assert 'public/img/materiales/' in html_client, "El catálogo del cliente debe renderizar las rutas de fotos de materiales"
    print("[OK] Panel del Cliente renderiza las fotos de los materiales en el catálogo.")

    # 5. Probar Login como Admin y verificar inventario, ojito, contraseña automática y barra de confiabilidad
    with client.session_transaction() as sess:
        sess['id_usuario'] = 1
        sess['username'] = 'joel_admin'
        sess['rol_name'] = 'Administrador'
        sess['id_persona'] = 1

    res_admin = client.get('/admin/dashboard')
    assert res_admin.status_code == 200
    html_admin = res_admin.data.decode('utf-8')
    
    # Miniaturas en tabla inventario
    assert 'product-thumb-table' in html_admin, "La tabla de inventario del admin debe tener miniaturas de fotos"
    # Ojito en admin
    assert 'togglePasswordVisibility' in html_admin, "El panel admin debe soportar ojito para ver contraseñas"
    # Contraseña automática
    assert 'generateAutoPassword' in html_admin, "El panel admin debe tener la función generateAutoPassword"
    assert 'Generar Automática' in html_admin, "El panel admin debe tener el botón para generar contraseña automática"
    # Barra de confiabilidad
    assert 'checkPasswordReliability' in html_admin, "El panel admin debe tener la función checkPasswordReliability"
    assert 'strength-bar-track' in html_admin, "El panel admin debe tener la barra track de confiabilidad"
    assert 'strength-bar-fill' in html_admin, "El panel admin debe tener el fill de la barra de confiabilidad"
    assert 'c_pass_strength_text' in html_admin, "El panel admin debe tener el texto indicador de confiabilidad"
    
    print("[OK] Panel de Administración contiene:")
    print("   - Miniaturas de fotos de los materiales en la tabla de inventario")
    print("   - Ojito para mostrar/ocultar contraseña")
    print("   - Generador de contraseña automática")
    print("   - Barra interactiva de confiabilidad de contraseña")

    print("\n========================================")
    print("   ¡TODAS LAS PRUEBAS PASARON CON ÉXITO! ")
    print("========================================")

if __name__ == '__main__':
    test_all()
