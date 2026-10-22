import random
from flask import session

# Mapeo de categorías a sus respectivos archivos de imagen
CAPTCHA_CATEGORIES = {
    "escaleras": ["stairs_1.jpg", "stairs_2.jpg", "stairs_3.jpg"],
    "herramientas": ["hammer.jpg", "shovel.jpg", "wheelbarrow.jpg"],
    "materiales": ["cement.jpg", "brick.jpg"],
    "seguridad": ["helmet.jpg"]
}

# Nombres legibles en español de los retos
CATEGORY_NAMES = {
    "escaleras": "escaleras",
    "herramientas": "herramientas de construcción",
    "materiales": "materiales de construcción (cemento/ladrillo)",
    "seguridad": "elementos de seguridad (cascos)"
}

ALL_IMAGES = [
    "stairs_1.jpg", "stairs_2.jpg", "stairs_3.jpg",
    "hammer.jpg", "shovel.jpg", "wheelbarrow.jpg",
    "cement.jpg", "brick.jpg", "helmet.jpg"
]

def get_captcha_challenge():
    """Genera un reto de selección de imágenes 3x3 y lo guarda en la sesión."""
    # Seleccionar categoría objetivo
    target_cat = random.choice(list(CAPTCHA_CATEGORIES.keys()))
    target_images = CAPTCHA_CATEGORIES[target_cat]
    
    # Barajar el orden de todas las 9 imágenes
    shuffled_images = list(ALL_IMAGES)
    random.shuffle(shuffled_images)
    
    # Encontrar índices correctos de las imágenes de la categoría objetivo
    correct_indices = []
    for idx, img in enumerate(shuffled_images):
        if img in target_images:
            correct_indices.append(idx)
            
    # Guardar en sesión
    session['captcha_indices'] = correct_indices
    session['captcha_target'] = CATEGORY_NAMES[target_cat]
    
    return CATEGORY_NAMES[target_cat], shuffled_images

def verify_captcha(selected_indices):
    """Verifica si la lista de índices seleccionados coincide con los correctos."""
    correct_indices = session.get('captcha_indices')
    if not correct_indices:
        return False
        
    try:
        # Convertir a sets para comparar sin importar el orden
        selected_set = set(int(i) for i in selected_indices)
        correct_set = set(int(i) for i in correct_indices)
        return selected_set == correct_set
    except Exception:
        return False
