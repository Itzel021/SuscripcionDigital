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
                <form action="./Backend/buscadorSuscrito.php" method="post">
                    <label for="categoria" class="me-2"><i class="fas fa-list"></i> Selecciona una Categoría:</label>
                    <div class="form-group">
                        <select id="categoria" name="categoria" class="form-control">
                            <option value="">Categorias disponibles</option>
                            <option value="libros">Libros</option>
                            <option value="revistas">Revistas</option>
                            <option value="mangas">Mangas</option>
                        </select>
                    </div>
                    <!-- Buscar por Titulo -->
                    <label for="isbn" class="me-2"><i class="fas fa-search"></i> Titulo:</label>
                    <div class="form-group d-flex align-items-center">
                        <input type="text" id="titulo" name="titulo" placeholder="escribe el titulo..."
                            class="form-control me-2" required>
                        <button class="btn btn-success" type="submit">BUSCAR</button>
                    </div>
                </form>
                <div class="alert alert-info mx-auto">
                    <strong>Resultado de búsqueda</strong>
                    <p>
                    <?php
                        echo isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : '';
                        unset($_SESSION['mensaje']);
                        ?>
                    </p>
                </div>
            </div>
            <div class="col-md-6 mx-auto text-center">
                <div class="card ml-5">
                    <!-- Mostrar imagen predeterminada o portada -->
                    <img src="./img/<?php echo isset($_SESSION['producto'][0]['Portada']) ? $_SESSION['producto'][0]['Portada'] : 'default'; ?>.jpg" 
                         class="card-img-top" alt="Portada">
                    <div class="mt-2">
                        <!-- Mostrar botones solo si hay un producto disponible -->
                        <?php if (isset($_SESSION['producto'][0])): ?>
                            <div class="card-body row">
                                <button class="btn btn-warning" data-toggle="modal" data-target="#modalDetalles"
                                    data-titulo="<?php echo htmlspecialchars($_SESSION['producto'][0]['Titulo'] ?? ''); ?>"
                                    data-isbn="<?php echo htmlspecialchars($_SESSION['producto'][0]['ISBN'] ?? ''); ?>"
                                    data-autor="<?php echo htmlspecialchars($_SESSION['detalles'][$_SESSION['producto'][0]['ISBN']]['Autor'] ?? ''); ?>"
                                    data-editorial="<?php echo htmlspecialchars($_SESSION['detalles'][$_SESSION['producto'][0]['ISBN']]['Editorial'] ?? ''); ?>"
                                    data-fecha="<?php echo htmlspecialchars($_SESSION['detalles'][$_SESSION['producto'][0]['ISBN']]['Fecha'] ?? ''); ?>"
                                    data-precio="<?php echo htmlspecialchars($_SESSION['detalles'][$_SESSION['producto'][0]['ISBN']]['Precio'] ?? ''); ?>">
                                    Detalles
                                </button>
                                <a href="contenido.php?url=<?php echo urlencode('./pdf/' . $_SESSION['detalles'][$_SESSION['producto'][0]['ISBN']]['URL'] . '.pdf'); ?>" 
                                   class="btn btn-primary ml-2">
                                    Leer
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
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
