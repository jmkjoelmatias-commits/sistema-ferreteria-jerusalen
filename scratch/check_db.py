import sys, os
sys.path.insert(0, os.path.abspath('.'))
from app import create_app
from database import get_db

app = create_app()
with app.app_context():
    cur = get_db().cursor()
    cur.execute("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'productos' ORDER BY ordinal_position;")
    print("Columnas actuales de productos:")
    for row in cur.fetchall():
        print(row)
