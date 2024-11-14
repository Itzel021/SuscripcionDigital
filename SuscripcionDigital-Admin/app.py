from flask import Flask, jsonify, request
import firebase_admin
from firebase_admin import credentials, db

app = Flask(__name__)

# Configuración de Firebase con tus credenciales y la URL de la base de datos
cred = credentials.Certificate("credenciales-json/suscripcion-digital-firebase-adminsdk-vx0b1-4058fb614f.json")
firebase_admin.initialize_app(cred, {
    "databaseURL": "https://suscripcion-digital-default-rtdb.firebaseio.com/"
})

# Función para generar el siguiente ID de libro
def generar_nuevo_isbn():
    # Referencia a la colección 'libros' en Firebase
    ref_detalles = db.reference("detalles")
    libros = ref_detalles.get()

    if libros:
        # Obtiene los IDs actuales, ordenarlos y seleccionar el último
        ultimo_isbn = max(libros.keys())
        numero_actual = int(ultimo_isbn.replace("LIB", ""))
        nuevo_isbn = f"LIB{str(numero_actual + 1).zfill(3)}"
    else:
        # Si no hay libros, comienza con LIB001
        nuevo_isbn = "LIB001"
    
    return nuevo_isbn

# Endpoint para agregar un libro
@app.route("/agregar_libro", methods=["POST"])
def agregar_libro():
    datos = request.json
    
    # Verifica que todos los campos necesarios estén presentes en el JSON
    if not all(k in datos for k in ("Autor", "Editorial", "Fecha", "Nombre", "Precio")):
        return jsonify({"error": "Faltan campos en la información del libro"}), 400
    
    nuevo_isbn = generar_nuevo_isbn()
    # Crea una nueva entrada en la colección 'libros'
    ref_detalles = db.reference("detalles")
    ref_detalles.child(nuevo_isbn).set(datos)
    
    ref_productos = db.reference("productos/libros")
    ref_productos.child(nuevo_isbn).set(datos["Nombre"])
    
    return jsonify({"message": "Libro agregado con éxito", "ISBN": nuevo_isbn})

#Endpoint para actualizar un libro
@app.route("/actualizar_libro/<isbn>", methods=["PUT"])
def actualizar_libro(isbn):
    datos_actualizados = request.json
    
    # Verificar si todos los campos requeridos están presentes en la solicitud
    if not all(k in datos_actualizados for k in ("Autor", "Editorial", "Fecha", "Nombre", "Precio")):
        return jsonify({"error": "Faltan campos en la información del libro"}), 400

    # Referencia al libro en "detalles" para verificar si existe
    ref_detalles = db.reference(f"detalles/{isbn}")
    libro_existente = ref_detalles.get()

    if libro_existente:
        # Actualizar la información en "detalles"
        ref_detalles.update(datos_actualizados)

        # Actualizar el nombre del libro en "productos/libros"
        ref_productos = db.reference(f"productos/libros/{isbn}")
        ref_productos.set(datos_actualizados["Nombre"])

        return jsonify({"message": f"Libro con ISBN {isbn} actualizado exitosamente."}), 200
    else:
        return jsonify({"error": "El libro no existe en la base de datos."}), 404

# Endpoint para eliminar un libro
@app.route("/eliminar_libro/<isbn>", methods=["DELETE"])
def eliminar_libro(isbn):
    # Referencia a la sección "detalles" donde están los datos del libro
    ref_detalles = db.reference(f"detalles/{isbn}")
    libro = ref_detalles.get()
    
    if libro:
        # Elimina el libro de "detalles"
        ref_detalles.delete()
        
        # Elimina la entrada en "productos/libros"
        ref_productos = db.reference(f"productos/libros/{isbn}")
        ref_productos.delete()
        
        return jsonify({"message": f"Libro con ISBN {isbn} eliminado exitosamente."}), 200
    else:
        return jsonify({"error": "El libro no existe en la base de datos."}), 404


# Inicia el servidor de Flask en el puerto 4000
if __name__ == "__main__":
    app.run(debug=True, port=4000)
