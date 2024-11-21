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

        .card-title {
            color: #8b1865;
        }
        .card{
            width: 400px;
        }
        .card img {
            width: 400px;
            height: 500px;
        }
    </style>
</head>
<?php
session_start();
?>

<body>
    <div class="container-fluid">
        <div class="sidebar">
            <div class="sidebar-content">
                <div class="message">
                    <h3>¡Bienvenido!</h3>
                </div>
                <div class="menu">
                    <a href="index.php"><button class="btn btn-light">BUSCAR LIBROS</button></a>
                    <a href="acceder.html"><button class="btn btn-light">ADQUIRIR LIBROS</button></a>
                </div>
            </div>
        </div>
        <div class="container d-flex main-content">
            <div class="col-md-6 mx-auto">
                <h1 class="text-center">Búsqueda de Libros</h1>
                <form action="./Backend/buscadorCliente.php" method="post">
                    <label for="categoria" class="me-2"><i class="fas fa-list"></i> Selecciona una Categoría:</label>
                    <div class="form-group">
                        <select id="categoria" name="categoria" class="form-control">
                            <option value="">Categorias disponibles</option>
                            <option value="libros">Libros</option>
                            <option value="revistas">Revistas</option>
                            <option value="mangas">Mangas</option>
                        </select>
                    </div>
                    <!-- Buscar por ISBN -->
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
                <div class="card">
                    <img src="./img/<?php
                    if (isset($_SESSION['portada'])) {
                        echo $_SESSION['portada'];
                        unset($_SESSION['portada']);
                    } else {
                        echo 'default';
                    }
                    ?>.jpg" class="card-img-top" alt="Portada">

                    <div class="mt-2">
                        <h5 class="card-title">
                            <?php
                            if (isset($_SESSION['ISBN'])) {
                                echo $_SESSION['ISBN'];
                                unset($_SESSION['ISBN']);
                            }
                            ?> :
                            <?php
                            if (isset($_SESSION['titulo'])) {
                                echo $_SESSION['titulo'];
                                unset($_SESSION['titulo']);
                            }
                            ?>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
</body>

</html>