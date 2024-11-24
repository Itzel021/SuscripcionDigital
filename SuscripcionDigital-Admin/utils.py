# utils.py
import firebase_admin
import hashlib
from firebase_admin import credentials, db

# Configuración de Firebase con tus credenciales y la URL de la base de datos
cred = credentials.Certificate("Credenciales/suscripcion-digital-firebase-adminsdk-vx0b1-4058fb614f.json")
firebase_admin.initialize_app(cred, {
    "databaseURL": "https://suscripcion-digital-default-rtdb.firebaseio.com/"
})

# Cargar respuestas desde Firebase
def cargar_respuestas():
    ref_respuestas = db.reference("respuestas")
    return ref_respuestas.get()

# Cache de respuestas
RESPUESTAS = cargar_respuestas()

# Obtener mensaje por código de respuesta
def obtener_respuesta(codigo):
    return RESPUESTAS.get(str(codigo), "Respuesta no definida.")

def verificar_admin(usuario, password):
    # Referencia a los usuarios
    ref_usuarios = db.reference("usuarios")
    usuarios = ref_usuarios.get()
    
    if usuario in usuarios:
        # Convertir la contraseña ingresada a MD5
        password_md5 = hashlib.md5(password.encode()).hexdigest()
        if usuarios[usuario] == password_md5:
            return True
        else:
            return False
    return False

def generar_nuevo_id(categoria):
    # Define los prefijos según la categoría
    prefijos = {
        "revista": "REV",
        "libro": "LIB",
        "manga": "MAN"
    }
    
    # Verifica si la categoría es válida
    if categoria not in prefijos:
        return None  # Maneja esto en el endpoint para devolver un error
    
    # Define la referencia a la colección `detalles`
    ref_detalles = db.reference("detalles")
    productos = ref_detalles.get()
    # Encuentra el último ID de la categoría
    if productos:
        ids_categoria = [k for k in productos.keys() if k.startswith(prefijos[categoria])]
        if ids_categoria:
            ultimo_id = max(ids_categoria)
            numero_actual = int(ultimo_id.replace(prefijos[categoria], ""))
            nuevo_id = f"{prefijos[categoria]}{str(numero_actual + 1).zfill(3)}"
        else:
            # Si no hay IDs en esta categoría, empieza con el primer ID
            nuevo_id = f"{prefijos[categoria]}001"
    else:
        nuevo_id = f"{prefijos[categoria]}001"
    return nuevo_id
