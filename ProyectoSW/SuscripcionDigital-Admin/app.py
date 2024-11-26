from flask import request
from flask import Flask, Response
from crud import agregar_producto, actualizar_producto, eliminar_producto, obtener_producto, obtener_todos_productos, obtener_productos_categoria

app = Flask(__name__)

@app.route("/productos", methods=["POST"])
def agregar():
    return agregar_producto()

@app.route("/productos/<string:producto_id>", methods=["PUT"])
def actualizar(producto_id):
    return actualizar_producto(producto_id)

@app.route("/productos/<string:producto_id>", methods=["DELETE"])
def eliminar(producto_id):
    return eliminar_producto(producto_id)

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
