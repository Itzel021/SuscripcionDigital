<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca Digital</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="./estilos/index.css">
    <style>
        body {
            margin: 0;
            overflow-x: hidden;
        }

        .catalog-wrapper {
            display: flex;
            overflow-x: auto;
            white-space: nowrap;
            padding: 10px 0;
        }

        .catalog-wrapper::-webkit-scrollbar {
            height: 8px;
        }

        .catalog-wrapper::-webkit-scrollbar-thumb {
            background-color: #888;
            border-radius: 4px;
        }

        .card {
            width: 300px;
            flex: 0 0 auto;
            margin-right: 10px;
        }

        .menu {
            margin-top: 360px;
        }
    </style>
</head>

<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
?>

<body>
    <div class="container-fluid">
        <div class="sidebar">
            <div class="sidebar-content">
                <div class="message">
                    <h5 class="mt-0">¡Bienvenid@, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h5>
                </div>
                <div class="menu">
                    <a href="buscar.php"><button class="btn btn-light">BUSCADOR DE PRODUCTOS</button></a>
                    <a href="catalogo.php"><button class="btn btn-light">CATÁLOGO DE PRODUCTOS</button></a>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
                </div>
            </div>
        </div>
        <div class="container d-flex main-content">
            <div class="col-md-6 mx-auto">
                <h1 class="text-center">Búsqueda de Libros</h1>
                <form id="buscadorForm">
                    <label for="categoria" class="me-2"><i class="fas fa-list"></i> Selecciona una Categoría:</label>
                    <div class="form-group">
                        <select id="categoria" name="categoria" class="form-control">
                            <option value="">Categorias disponibles</option>
                            <option value="libros">Libros</option>
                            <option value="revistas">Revistas</option>
                            <option value="mangas">Mangas</option>
                        </select>
                    </div>
                    <label for="titulo" class="me-2"><i class="fas fa-search"></i> Titulo:</label>
                    <div class="form-group d-flex align-items-center">
                        <input type="text" id="titulo" name="titulo" placeholder="escribe el titulo..."
                            class="form-control me-2" required>
                        <button class="btn btn-success" type="submit">BUSCAR</button>
                    </div>
                </form>
                <div id="message" class="alert d-none" role="alert"></div>
            </div>
            <div class="col-md-6 mx-auto text-center">
                <div id="resultados" class="mt-4">
                    <!-- Los resultados se cargarán aquí -->
                </div>

            </div>
        </div>
    </div>

    <!-- Modal de Detalles del Producto -->
    <div class="modal fade" id="modalDetalles" tabindex="-1" role="dialog" aria-labelledby="modalDetallesLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetallesLabel">Detalles del Libro</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Título:</strong> <span id="modalTitulo"></span></p>
                    <p><strong>ISBN:</strong> <span id="modalISBN"></span></p>
                    <p><strong>Autor:</strong> <span id="modalAutor"></span></p>
                    <p><strong>Editorial:</strong> <span id="modalEditorial"></span></p>
                    <p><strong>Fecha de Publicación:</strong> <span id="modalFecha"></span></p>
                    <p><strong>Precio:</strong> <span id="modalPrecio"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts de Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        document.getElementById('buscadorForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const messageDiv = document.getElementById('message');
            const categoria = document.getElementById('categoria').value.trim();
            const titulo = document.getElementById('titulo').value.trim();

            // Mostrar mensaje de carga
            messageDiv.className = 'alert alert-info';
            messageDiv.textContent = 'Buscando...';
            messageDiv.classList.remove('d-none');

            fetch(`/ws/SuscripcionDigital/ClientePHP/detalles?categoria=${encodeURIComponent(categoria)}&titulo=${encodeURIComponent(titulo)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la búsqueda');
                    }
                    return response.json();
                })
                .then(data => {
                    const messageDiv = document.getElementById('message');
                    const resultadosDiv = document.getElementById('resultados');
                    resultadosDiv.innerHTML = '';

                    if (data.producto && data.producto.length > 0) {
                        const producto = data.producto[0];
                        const detalles = data.detalles[producto.ISBN];

                        resultadosDiv.innerHTML = `
                        <div class="card ml-3">
                            <img src="./img/${producto.Portada || 'default'}.jpg" class="card-img-top" alt="Portada">
                            <div class="card-body">
                                <button 
                                    class="btn btn-warning ml-2"
                                    data-toggle="modal"
                                    data-target="#modalDetalles"
                                    data-titulo="${producto.Titulo}"
                                    data-isbn="${producto.ISBN}"
                                    data-autor="${detalles.Autor || 'Desconocido'}"
                                    data-editorial="${detalles.Editorial || 'Desconocida'}"
                                    data-fecha="${detalles.Fecha || 'Desconocida'}"
                                    data-precio="${detalles.Precio || '0.00'}">
                                    Detalles
                                </button>
                                <a href="contenido.php?url=${encodeURIComponent('./pdf/' + detalles.URL + '.pdf')}" class="btn btn-primary">Leer</a>
                            </div>
                        </div>
                    `;

                        messageDiv.classList.add('alert-success');
                        messageDiv.textContent = data.message;  // Mostrar mensaje de éxito
                    } else {
                        messageDiv.classList.add('alert-danger');
                        messageDiv.textContent = data.error;  // Mensaje de error
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const messageDiv = document.getElementById('message');
                    messageDiv.classList.remove('d-none', 'alert-success');
                    messageDiv.classList.add('alert-danger');
                    messageDiv.textContent = '305 : Título no disponible';
                });
        });

        // Script para pasar los datos al modal
        $('#modalDetalles').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Botón que abre el modal
            var titulo = button.data('titulo');
            var isbn = button.data('isbn');
            var autor = button.data('autor');
            var editorial = button.data('editorial');
            var fecha = button.data('fecha');
            var precio = button.data('precio');

            // Colocar los datos en el modal
            var modal = $(this);
            modal.find('#modalTitulo').text(titulo);
            modal.find('#modalISBN').text(isbn);
            modal.find('#modalAutor').text(autor);
            modal.find('#modalEditorial').text(editorial);
            modal.find('#modalFecha').text(fecha);
            modal.find('#modalPrecio').text(precio);
        });

    </script>

</body>

</html>