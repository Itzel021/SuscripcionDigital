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

    # Verifica que los campos obligatorios del producto estén presentes
    producto = datos.get("producto", {})
    if not all(k in producto for k in ("Autor", "Editorial", "Fecha", "Nombre", "Precio", "Categoria", "URL")):
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
        "Descuento": producto.get("Descuento", 0),
        "URL": producto["URL"]
    })

    # Inserta el producto en la categoría correspondiente
    ref_productos.child(nuevo_id).set(producto["Nombre"])

    return jsonify({
        "codigo": 202,
        "mensaje": obtener_respuesta(202),  # Producto registrado correctamente
        "ID": nuevo_id
    }), 201

#Endpoint para actualizar un producto
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
    if not all(k in datos_actualizados for k in ("Autor", "Editorial", "Fecha", "Nombre", "Precio", "URL")):
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