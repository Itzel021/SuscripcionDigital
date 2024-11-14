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
            width: 150px;
            flex: 0 0 auto;
            margin-right: 10px;
        }
        .menu{
            margin-top: 360px;
        }
    </style>
</head>
<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.html");
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
                    <a href="buscar.php"><button class="btn btn-light">BUSCAR LIBROS</button></a>
                    <a href="usuario.php"><button class="btn btn-light">ADQUIRIR LIBROS</button></a>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
                </div>
            </div>
        </div>
        <div class="main-content">
            <div class="col-md-8 mx-auto">
                <h1 class="text-center">Búsqueda de Libros</h1>
                <label for="categoria"><i class="fas fa-search"></i> ¿Qué título buscas?</label>
                <div class="form-group d-flex">
                    <input type="text" id="categoria" placeholder="Escribe..." class="form-control">
                    <button class="btn btn-success">BUSCAR</button>
                </div>
                <p id="resutado-busqueda"></p>
                <h3 class="text-center">Catálogo de Productos</h3>
                <div class="catalog-wrapper">
                    <div class="card">
                        <img src="./img/portadaFUNGIRL.jpg" alt="Portada del libro" class="card-img-top rounded">
                    </div>
                    <div class="card">
                        <img src="./img/portadaARRANCAMELAVIDA.jpg" alt="Portada del libro 2"
                            class="card-img-top rounded">
                    </div>
                    <div class="card">
                        <img src="./img/portadaAMORCOLERA.jpg" alt="Portada del libro 3" class="card-img-top rounded">
                    </div>
                    <div class="card">
                        <img src="./img/portadaFABRICANTEDELAGRIMAS.jpg" alt="Portada del libro"
                            class="card-img-top rounded">
                    </div>
                    <div class="card">
                        <img src="./img/portadaMUJERCITAS.jpg" alt="Portada del libro 2" class="card-img-top rounded">
                    </div>
                    <div class="card">
                        <img src="./img/portadaORGULLOPREJUICIO.jpg" alt="Portada del libro 3"
                            class="card-img-top rounded">
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>