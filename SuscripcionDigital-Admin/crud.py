import requests
from flask import jsonify, request
from utils import obtener_respuesta, verificar_admin, generar_nuevo_id
from firebase_admin import db

# Endpoint para agregar un producto
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

    producto = datos.get("producto", {})
    if not all(k in producto for k in ("Autor", "Editorial", "Fecha", "Nombre", "Precio", "Categoria", "URL", "Portada")):
        return jsonify({
            "codigo": 303,
            "mensaje": obtener_respuesta(303)  # JSON mal formado
        }), 400

    # Genera el ID según la categoría
    categoria = producto.get("Categoria", "").lower()  # Asegura que esté en minúsculas
    nuevo_id = generar_nuevo_id(categoria)

    if not nuevo_id:
        return jsonify({
            "codigo": 300,
            "mensaje": obtener_respuesta(300)  # Categoría no válida
        }), 400

    ref_detalles = db.reference("detalles")
    ref_productos = db.reference(f"productos/{categoria}s")

    if ref_detalles.child(nuevo_id).get():
        return jsonify({
            "codigo": 302,
            "mensaje": obtener_respuesta(302)  # Producto ya existe
        }), 409

    # Inserta el producto
    ref_detalles.child(nuevo_id).set({
        "Autor": producto["Autor"],
        "Editorial": producto["Editorial"],
        "Fecha": producto["Fecha"],
        "Nombre": producto["Nombre"],
        "Precio": producto["Precio"],
        "Descuento": producto.get("Descuento", 0),
        "URL": producto["URL"],
        "Portada": producto["Portada"].lower()
    })

    ref_productos.child(nuevo_id).set(producto["Nombre"])
    webhook_url = "http://localhost/ws/SuscripcionDigital/ClientePHP/webhook_handler"
    webhook_data = {
        "evento": "nuevo_producto",
        "producto": {
            "Nombre": producto["Nombre"],
            "Autor": producto["Autor"],
            "Precio": producto["Precio"],
            "URL": producto["URL"]
        }
    }
    try:
        # Realizar la solicitud POST al webhook
        response = requests.post(webhook_url, json=webhook_data)

        # Verificar si el webhook fue exitoso
        if response.status_code == 200:
            print("Webhook enviado correctamente")
        else:
            print(f"Error al enviar el webhook: {response.status_code}")
    except requests.exceptions.RequestException as e:
        print(f"Error al realizar la solicitud al webhook: {str(e)}")
    
    return jsonify({
        "codigo": 202,
        "mensaje": obtener_respuesta(202),  # Producto registrado correctamente
        "ID": nuevo_id
    }), 201

#Endpoint para actualizar un producto
def actualizar_producto(producto_id):
    datos = request.json

    # Verificar administrador
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

    campos_validos = {"Autor", "Editorial", "Fecha", "Nombre", "Precio", "URL", "Portada", "Descuento"}

    datos_actualizados = datos.get("producto", {})
    
    # Detectar campos desconocidos
    campos_proporcionados = set(datos_actualizados.keys())
    campos_desconocidos = campos_proporcionados - campos_validos
    if campos_desconocidos:
        return jsonify({
            "codigo": 306,
            "mensaje": f"El JSON contiene campos desconocidos: {', '.join(campos_desconocidos)}"
        }), 400

    datos_filtrados = {k: v for k, v in datos_actualizados.items() if k in campos_validos}

    if not datos_filtrados:
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

    ref_detalles = db.reference(f"detalles/{producto_id}")
    producto_existente = ref_detalles.get()

    if not producto_existente:
        return jsonify({
            "codigo": 301,
            "mensaje": obtener_respuesta(301)  # Producto no encontrado
        }), 404

    if "Portada" in datos_filtrados:
        portada = datos_filtrados["Portada"]
        if not isinstance(portada, str) or not portada.strip():
            return jsonify({
                "codigo": 305,
                "mensaje": "El campo 'Portada' debe ser una cadena no vacía."
            }), 400
        datos_filtrados["Portada"] = portada.lower()

    try:
        # Actualizar producto
        ref_detalles.update(datos_filtrados)
        ref_productos = db.reference(f"productos/{categoria}/{producto_id}")
        ref_productos.set(datos_filtrados.get("Nombre", producto_existente.get("Nombre")))

        return jsonify({
            "codigo": 203,
            "mensaje": obtener_respuesta(203),  # Producto actualizado correctamente
            "ID": producto_id
        }), 200
    except Exception as e:
        return jsonify({
            "codigo": 500,
            "mensaje": f"Error interno al actualizar el producto: {str(e)}"
        }), 500       

# Endpoint para eliminar un producto
def eliminar_producto(producto_id):
    datos = request.json

    usuario = datos.get("usuario")
    password = datos.get("password")

    if not usuario or not password:
        return jsonify({
            "codigo": 500,
            "mensaje": obtener_respuesta(500) # Usuario no reconocido
        }), 401
        
    if not verificar_admin(usuario, password):
        return jsonify({
            "codigo": 501,
            "mensaje": obtener_respuesta(501)  # Contraseña no reconocida
        }), 403

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

    ref_detalles = db.reference(f"detalles/{producto_id}")
    producto = ref_detalles.get()

    if producto:
        # Eliminar el producto
        ref_detalles.delete()
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

# Función para obtener todos los productos
def obtener_todos_productos():
    ref_productos = db.reference("productos")
    productos = ref_productos.get()

    if productos:
        return jsonify({
            "codigo": 200,
            "mensaje": obtener_respuesta(200),  # Categoría encontrada exitosamente
            "productos": productos
        }), 200
    else:
        return jsonify({
            "codigo": 300,
            "mensaje": obtener_respuesta(300)  # Categoria no encontrada
        }), 404

# Función para obtener los productos de una categoría
def obtener_productos_categoria(categoria):
    ref_productos = db.reference(f"productos/{categoria}")
    productos_categoria = ref_productos.get()

    if productos_categoria:
        return jsonify({
            "codigo": 306,
            "mensaje": obtener_respuesta(306),
            "productos": productos_categoria
        }), 200
    else:
        return jsonify({
            "codigo": 300,
            "mensaje": obtener_respuesta(300)
        }), 404

# Función para obtener un producto
def obtener_producto(producto_id):
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

    ref_detalles = db.reference(f"detalles/{producto_id}")
    producto = ref_detalles.get()

    if producto:
        return jsonify({
            "codigo": 201,
            "mensaje": obtener_respuesta(201),  # Título encontrado exitosamente
            "producto": producto
        }), 200
    else:
        return jsonify({
            "codigo": 301,
            "mensaje": obtener_respuesta(301)  # ISBN no encontrado
        }), 404
        



