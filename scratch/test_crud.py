import requests
import psycopg2

BASE_URL = "http://127.0.0.1:8000"

def inspect_db_state():
    conn = psycopg2.connect(host='localhost', user='postgres', password='tico565mati', dbname='ferreteria_db')
    cur = conn.cursor()
    try:
        cur.execute("SELECT id_producto, codigo_barras, nombre_producto, precio_venta, stock_actual, stock_minimo FROM productos ORDER BY id_producto DESC LIMIT 1;")
        last_prod = cur.fetchone()
        
        cur.execute("""
            SELECT u.id_usuario, u.username, p.nombre_completo, p.ci_nit, r.nombre_rol
            FROM usuarios u
            JOIN personas p ON u.id_persona = p.id_persona
            JOIN roles r ON u.id_rol = r.id_rol
            ORDER BY u.id_usuario DESC LIMIT 1;
        """)
        last_user = cur.fetchone()
        
        return last_prod, last_user
    finally:
        cur.close()
        conn.close()

def main():
    print("--- STARTING INTEGRATION TEST ---")
    session = requests.Session()
    
    # 1. Login as admin
    login_data = {
        'username': 'joel_admin',
        'password': 'admin123',
        'rol': 'Administrador'
    }
    r = session.post(f"{BASE_URL}/", data=login_data)
    print("Login Response Status:", r.status_code)
    if "Panel Control" in r.text or "joel_admin" in r.text:
        print("✔ Login successful!")
    else:
        print("✘ Login failed!")
        return
        
    # 2. Add product
    prod_data = {
        'codigo_barras': '999111',
        'nombre_producto': 'Ladrillo Test IA',
        'precio_venta': '2.50',
        'stock_actual': '150',
        'stock_minimo': '20',
        'id_categoria': '1'  # Obra Gruesa
    }
    r = session.post(f"{BASE_URL}/admin/inventory/create", data=prod_data)
    print("Create Product Status:", r.status_code)
    
    # 3. Create client
    client_data = {
        'username': 'pepe_test',
        'password': 'pepe123password',
        'ci_nit': '888888',
        'nombre_completo': 'Pepe Test Client',
        'telefono': '76543299',
        'direccion': 'Calle Falsa 123'
    }
    r = session.post(f"{BASE_URL}/admin/clients/create", data=client_data)
    print("Create Client Status:", r.status_code)
    
    # Check DB state
    last_prod, last_user = inspect_db_state()
    print("Last Product in DB:", last_prod)
    print("Last User in DB:", last_user)
    
    # 4. Edit product
    if last_prod and last_prod[2] == 'Ladrillo Test IA':
        prod_id = last_prod[0]
        edit_prod_data = {
            'codigo_barras': '999111-updated',
            'nombre_producto': 'Ladrillo Test IA Modificado',
            'precio_venta': '3.20',
            'stock_actual': '200',
            'stock_minimo': '25',
            'id_categoria': '1'
        }
        r = session.post(f"{BASE_URL}/admin/inventory/edit/{prod_id}", data=edit_prod_data)
        print("Edit Product Status:", r.status_code)
        
    # 5. Edit user details (and optionally password/role)
    if last_user and last_user[1] == 'pepe_test':
        user_id = last_user[0]
        edit_user_data = {
            'username': 'pepe_test_mod',
            'nombre_completo': 'Pepe Test Client Modificado',
            'ci_nit': '888888-M',
            'telefono': '76543290',
            'direccion': 'Calle Verdadera 456',
            'id_rol': '2',  # Cliente
            'password': ''  # Do not change password
        }
        r = session.post(f"{BASE_URL}/admin/users/edit/{user_id}", data=edit_user_data)
        print("Edit User Status:", r.status_code)
        
    # Check DB state again
    last_prod, last_user = inspect_db_state()
    print("Last Product in DB after Edit:", last_prod)
    print("Last User in DB after Edit:", last_user)
    
    # 6. Delete newly created product
    if last_prod and 'Ladrillo Test' in last_prod[2]:
        prod_id = last_prod[0]
        r = session.get(f"{BASE_URL}/admin/inventory/delete/{prod_id}")
        print("Delete Product Status:", r.status_code)
        
    # 7. Delete newly created user (which cascades to persona/client)
    if last_user and 'pepe_test' in last_user[1]:
        user_id = last_user[0]
        r = session.get(f"{BASE_URL}/admin/users/delete/{user_id}")
        print("Delete User Status:", r.status_code)

    print("--- INTEGRATION TEST FINISHED ---")

if __name__ == "__main__":
    main()
