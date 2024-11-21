# app.py
from flask import Flask
from crud import agregar_producto, actualizar_producto, eliminar_producto
from utils import obtener_respuesta

app = Flask(__name__)

@app.route("/agregar_producto", methods=["POST"])
def agregar():
    return agregar_producto()

@app.route("/actualizar_producto/<string:producto_id>", methods=["PUT"])
def actualizar(producto_id):
    return actualizar_producto(producto_id)

@app.route("/eliminar_producto/<string:producto_id>", methods=["DELETE"])
def eliminar(producto_id):
    return eliminar_producto(producto_id)

if __name__ == "__main__":
    app.run(debug=True, port=4000)