from flask import request, Flask, Response
from crud import (
    agregar_producto, actualizar_producto, eliminar_producto,
    obtener_producto, obtener_todos_productos, obtener_productos_categoria
)
from flask_cors import CORS
import time
import json

app = Flask(__name__)
clientes = []  # Almacena las notificaciones pendientes para los clientes
CORS(app)

@app.route("/productos", methods=["POST"])
def agregar():
    resultado, status_code, notificacion = agregar_producto()  # Captura todos los valores
    if status_code == 201:  # Producto agregado correctamente
        notificar_clientes(notificacion)  # Notificar a los clientes
    return resultado, status_code

@app.route("/notificaciones", methods=["GET"])
def notificaciones():
    def generar():
        while True:
            time.sleep(1)  # Pausa entre mensajes
            if clientes:
                mensaje = clientes.pop(0)  # Extrae la notificación más reciente
                yield f"data: {mensaje}\n\n"  # Enviar como string JSON válido
            else:
                yield f"data: heartbeat\n\n"  # Mantener la conexión activa
    return Response(generar(), content_type="text/event-stream")

def notificar_clientes(nueva_notificacion):
    mensaje = {
        "codigo": nueva_notificacion.get("codigo"),
        "titulo": nueva_notificacion.get("titulo"),
        "categoria": nueva_notificacion.get("categoria"),
    }
    clientes.append(json.dumps(mensaje))  # Convertir a JSON y agregar a la lista

@app.route("/productos/<string:producto_id>", methods=["PUT"])
def actualizar(producto_id):
    resultado, status_code = actualizar_producto(producto_id)
    return resultado, status_code

@app.route("/productos/<string:producto_id>", methods=["DELETE"])
def eliminar(producto_id):
    resultado, status_code = eliminar_producto(producto_id)
    return resultado, status_code

@app.route('/productos', methods=['GET'])
def obtener_productos():
    producto_id = request.args.get('id')
    categoria = request.args.get('categoria')
    if producto_id:
        return obtener_producto(producto_id)
    elif categoria:
        return obtener_productos_categoria(categoria)
    else:
        return obtener_todos_productos()

if __name__ == "__main__":
    app.run(debug=True, port=4000)
