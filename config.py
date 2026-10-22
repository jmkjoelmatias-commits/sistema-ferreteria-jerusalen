import os

class Config:
    # Llave secreta para el manejo de sesiones en Flask
    SECRET_KEY = os.environ.get('SECRET_KEY') or 'super-secret-key-agencia-jerusalen-12345'
    
    # Credenciales de PostgreSQL (Configuración local pgAdmin)
    DB_HOST = os.environ.get('DB_HOST', 'localhost')
    DB_PORT = os.environ.get('DB_PORT', '5432')
    DB_NAME = os.environ.get('DB_NAME', 'ferreteria_db')
    DB_USER = os.environ.get('DB_USER', 'postgres')
    DB_PASS = os.environ.get('DB_PASS', 'tico565mati')
    
    # Teléfono del Administrador para la API de redirección a WhatsApp Web
    WHATSAPP_ADMIN_PHONE = os.environ.get('WHATSAPP_ADMIN_PHONE', '59177755533') # Joel Matias Ticona por defecto
