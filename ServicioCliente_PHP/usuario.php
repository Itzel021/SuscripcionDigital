<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suscripción Digital</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="./estilos/index.css">
    <style>
        .menu { margin-top: 360px; }
        .card { width: 180px; height: 350px; }
        .card img { width: 180px; height: 300px; object-fit: cover; }
        .catalogo-container { max-height: 550px; overflow-y: auto; padding-right: 15px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="sidebar">
            <div class="sidebar-content">
                <h5 class="mt-0">¡Bienvenid@, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h5>
                <div class="menu">
                    <a href="buscar.php"><button class="btn btn-light">BUSCADOR DE PRODUCTOS</button></a>
                    <a href="./Backend/catalogo.php"><button class="btn btn-light">CATÁLOGO DE PRODUCTOS</button></a>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="col-md-12 mx-auto">
                <div class="catalogo-container">
                    <div class="row mt-4">
                        <?php if (!empty($_SESSION['detalles'])): ?>
                            <?php foreach ($_SESSION['detalles'] as $isbn => $detalles): ?>
                                <div class="col-md-3 mb-3">
                                    <div class="card">
                                        <img src="./img/<?php echo htmlspecialchars($detalles['Portada']); ?>.jpg"
                                            alt="Portadas" />
                                        <div class="card-body row">
                                            <button class="btn btn-warning" data-toggle="modal" data-target="#modalDetalles"
                                                data-titulo="<?php echo htmlspecialchars($detalles['Nombre']); ?>"
                                                data-isbn="<?php echo htmlspecialchars($isbn); ?>"
                                                data-autor="<?php echo htmlspecialchars($detalles['Autor']); ?>"
                                                data-editorial="<?php echo htmlspecialchars($detalles['Editorial']); ?>"
                                                data-fecha="<?php echo htmlspecialchars($detalles['Fecha']); ?>"
                                                data-precio="<?php echo htmlspecialchars($detalles['Precio']); ?>">Detalles</button>
                                            <a href="contenido.php?url=<?php echo urlencode('./pdf/' . $detalles['URL'] . '.pdf'); ?>"
                                                class="btn btn-primary ml-3">Leer</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info text-center">
                                <strong>¡Disfruta del contenido que tenemos para ti!</strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalles -->
    <div class="modal fade" id="modalDetalles" tabindex="-1" role="dialog" aria-labelledby="modalDetallesLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetallesLabel">Detalles del Libro</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Título:</strong> <span id="modalTitulo"></span></p>
                    <p><strong>ISBN:</strong> <span id="modalISBN"></span></p>
                    <p><strong>Autor:</strong> <span id="modalAutor"></span></p>
                    <p><strong>Editorial:</strong> <span id="modalEditorial"></span></p>
                    <p><strong>Fecha de Publicación:</strong> <span id="modalFecha"></span></p>
                    <p><strong>Precio:</strong> $<span id="modalPrecio"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $('#modalDetalles').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            $('#modalTitulo').text(button.data('titulo'));
            $('#modalISBN').text(button.data('isbn'));
            $('#modalAutor').text(button.data('autor'));
            $('#modalEditorial').text(button.data('editorial'));
            $('#modalFecha').text(button.data('fecha'));
            $('#modalPrecio').text(button.data('precio'));
        });
    </script>
</body>
</html>
