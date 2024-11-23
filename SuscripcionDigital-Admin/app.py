from flask import Flask, Response
from crud import agregar_producto, actualizar_producto, eliminar_producto
from utils import obtener_respuesta
from flask_cors import CORS
import time
import json

app = Flask(__name__)

# Almacena los clientes conectados
clientes = []
CORS(app)

@app.route("/agregar_producto", methods=["POST"])
def agregar():
    resultado = agregar_producto()
    if resultado[1] == 201:  # Si el producto se agregó correctamente
        notificar_clientes(resultado[0].get_json())  # Enviar notificación
    return resultado
    
@app.route("/notificaciones", methods=["GET"])
def notificaciones():
    def generar():
        while True:
            time.sleep(1)  # Pausa entre mensajes
            if clientes:
                mensaje = clientes.pop(0)  # Extrae la notificación más reciente
                yield f"data: {mensaje}\n\n"  # Enviar como string JSON válido
            else:
                 yield f"data: heartbeat\n\n"
            
    return Response(generar(), content_type="text/event-stream")


def notificar_clientes(nueva_notificacion):
    mensaje = {
        "codigo": nueva_notificacion.get("codigo"),
        "titulo": nueva_notificacion.get("titulo"),
        "categoria" : nueva_notificacion.get("categoria")
    }
    clientes.append(json.dumps(mensaje))  # Convertir a JSON antes de agregarlo a la lista


@app.route("/actualizar_producto/<string:producto_id>", methods=["PUT"])
def actualizar(producto_id):
    return actualizar_producto(producto_id)

@app.route("/eliminar_producto/<string:producto_id>", methods=["DELETE"])
def eliminar(producto_id):
    return eliminar_producto(producto_id)

if __name__ == "__main__":
    app.run(debug=True, port=4000)
