from flask import Flask, jsonify, request
import firebase_admin
import hashlib
from firebase_admin import credentials, db

app = Flask(__name__)

# Configuración de Firebase con tus credenciales y la URL de la base de datos
cred = credentials.Certificate("credenciales-json/suscripcion-digital-firebase-adminsdk-vx0b1-4058fb614f.json")
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

# Función para generar el siguiente ID del producto
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


# Endpoint para agregar un producto
@app.route("/agregar_producto", methods=["POST"])
def agregar_producto():
    datos = request.json

    # Verificar autenticación del administrador
    usuario = datos.get("usuario")
    password = datos.get("password")

    if not usuario or not password:
        return jsonify({
            "codigo": 500,
            "mensaje": obtener_respuesta(500)  # Usuario no reconocido
        }), 401

    if not verificar_admin(usuario, password):
        return jsonify({
            "codigo": 501,
            "mensaje": obtener_respuesta(501)  # Contraseña no reconocida
        }), 403

    # Verifica que los campos obligatorios del producto estén presentes
    producto = datos.get("producto", {})
    if not all(k in producto for k in ("Autor", "Editorial", "Fecha", "Nombre", "Precio", "Categoria")):
        return jsonify({
            "codigo": 303,
            "mensaje": obtener_respuesta(303)  # JSON mal formado
        }), 400

    # Genera el ID según la categoría
    categoria = producto.get("Categoria", "").lower()  # Asegura que esté en minúsculas
    nuevo_id = generar_nuevo_id(categoria)

    # Si la categoría no es válida, devuelve un error
    if not nuevo_id:
        return jsonify({
            "codigo": 300,
            "mensaje": obtener_respuesta(300)  # Categoría no válida
        }), 400

    # Referencias en Firebase
    ref_detalles = db.reference("detalles")
    ref_productos = db.reference(f"productos/{categoria}s")

    # Verifica si el producto ya existe
    if ref_detalles.child(nuevo_id).get():
        return jsonify({
            "codigo": 302,
            "mensaje": obtener_respuesta(302)  # Producto ya existe
        }), 409

    # Inserta el producto en 'detalles'
    ref_detalles.child(nuevo_id).set({
        "Autor": producto["Autor"],
        "Editorial": producto["Editorial"],
        "Fecha": producto["Fecha"],
        "Nombre": producto["Nombre"],
        "Precio": producto["Precio"],
        "Descuento": producto.get("Descuento", 0)  # Descuento opcional, por defecto 0
    })

    # Inserta el producto en la categoría correspondiente
    ref_productos.child(nuevo_id).set(producto["Nombre"])

    return jsonify({
        "codigo": 202,
        "mensaje": obtener_respuesta(202),  # Producto registrado correctamente
        "ID": nuevo_id
    }), 201


#Endpoint para actualizar un producto
@app.route("/actualizar_producto/<producto_id>", methods=["PUT"])
def actualizar_producto(producto_id):
    datos = request.json

    # Verificar autenticación del administrador
    usuario = datos.get("usuario")
    password = datos.get("password")

    if not usuario or not password:
        return jsonify({
            "codigo": 500,
            "mensaje": obtener_respuesta(500)  # Usuario no reconocido
        }), 401

    if not verificar_admin(usuario, password):
        return jsonify({
            "codigo": 501,
            "mensaje": obtener_respuesta(501)  # Contraseña no reconocida
        }), 403

    # Verifica que los campos obligatorios del producto estén presentes
    datos_actualizados = datos.get("producto", {})
    if not all(k in datos_actualizados for k in ("Autor", "Editorial", "Fecha", "Nombre", "Precio")):
        return jsonify({
            "codigo": 303,
            "mensaje": obtener_respuesta(303)  # JSON mal formado
        }), 400

    # Detectar categoría según el prefijo del ID
    if producto_id.startswith("REV"):
        categoria = "revistas"
    elif producto_id.startswith("LIB"):
        categoria = "libros"
    elif producto_id.startswith("MAN"):
        categoria = "mangas"
    else:
        return jsonify({
            "codigo": 304,
            "mensaje": obtener_respuesta(304)  # ID no corresponde a una categoría válida
        }), 400

    # Referencia al producto en "detalles"
    ref_detalles = db.reference(f"detalles/{producto_id}")
    producto_existente = ref_detalles.get()

    if producto_existente:
        # Actualizar los detalles del producto
        ref_detalles.update(datos_actualizados)

        # Actualizar el nombre del producto en "productos/{categoria}"
        ref_productos = db.reference(f"productos/{categoria}/{producto_id}")
        ref_productos.set(datos_actualizados["Nombre"])

        return jsonify({
            "codigo": 203,
            "mensaje": obtener_respuesta(203),  # Producto actualizado correctamente
            "ID": producto_id
        }), 200
    else:
        return jsonify({
            "codigo": 301,
            "mensaje": obtener_respuesta(301)  # ISBN no encontrado
        }), 404
        

# Endpoint para eliminar un producto
@app.route("/eliminar_producto/<producto_id>", methods=["DELETE"])
def eliminar_producto(producto_id):
    datos = request.json

    # Verificar autenticación del administrador
    usuario = datos.get("usuario")
    password = datos.get("password")

    if not usuario or not password:
        return jsonify({
            "codigo": 500,
            "mensaje": obtener_respuesta(500)  # Usuario no reconocido
        }), 401

    if not verificar_admin(usuario, password):
        return jsonify({
            "codigo": 501,
            "mensaje": obtener_respuesta(501)  # Contraseña no reconocida
        }), 403

    # Detectar categoría según el prefijo del ID
    if producto_id.startswith("REV"):
        categoria = "revistas"
    elif producto_id.startswith("LIB"):
        categoria = "libros"
    elif producto_id.startswith("MAN"):
        categoria = "mangas"
    else:
        return jsonify({
            "codigo": 304,
            "mensaje": obtener_respuesta(304)  # ID no corresponde a una categoría válida
        }), 400

    # Referencia al producto en "detalles"
    ref_detalles = db.reference(f"detalles/{producto_id}")
    producto = ref_detalles.get()

    if producto:
        # Eliminar el producto de "detalles"
        ref_detalles.delete()

        # Eliminar la entrada en "productos/{categoria}"
        ref_productos = db.reference(f"productos/{categoria}/{producto_id}")
        ref_productos.delete()

        return jsonify({
            "codigo": 204,
            "mensaje": obtener_respuesta(204),  # Producto borrado correctamente
            "ID": producto_id
        }), 200
    else:
        return jsonify({
            "codigo": 301,
            "mensaje": obtener_respuesta(301)  # ISBN no encontrado
        }), 404


# Inicia el servidor de Flask en el puerto 4000
if __name__ == "__main__":
    app.run(debug=True, port=4000)
