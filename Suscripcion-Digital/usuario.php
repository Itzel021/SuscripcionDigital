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
        .catalog-wrapper {
            padding: 10px 0;
        }

        .card {
            width: 100%;
            margin-bottom: 15px;
        }

        .card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
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
        <!-- Main Content -->
        <div class="main-content">
            <div class="col-md-12 mx-auto">
            <h1 class="text-center mb-4">Catálogo de Productos</h1>
            <div class="catalog-wrapper row">
                <div class="col-md-2">
                    <div class="card">
                        <img src="./img/portadaFUNGIRL.jpg" alt="Portada del libro" class="card-img-top rounded">
                        <a href="" class="btn btn-info mt-2">Leer</a>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <img src="./img/portadaARRANCAMELAVIDA.jpg" alt="Portada del libro 2"
                            class="card-img-top rounded">
                        <a href="" class="btn btn-info mt-2">Leer</a>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <img src="./img/portadaAMORCOLERA.jpg" alt="Portada del libro 3" class="card-img-top rounded">
                        <a href="" class="btn btn-info mt-2">Leer</a>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <img src="./img/portadaFABRICANTEDELAGRIMAS.jpg" alt="Portada del libro"
                            class="card-img-top rounded">
                        <a href="" class="btn btn-info mt-2">Leer</a>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <img src="./img/portadaMUJERCITAS.jpg" alt="Portada del libro 2" class="card-img-top rounded">
                        <a href="" class="btn btn-info mt-2">Leer</a>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <img src="./img/portadaORGULLOPREJUICIO.jpg" alt="Portada del libro 3"
                            class="card-img-top rounded">
                        <a href="" class="btn btn-info mt-2">Leer</a>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <img src="./img/portadaOSCUROS.jpg" alt="Portada del libro" class="card-img-top rounded">
                        <a href="" class="btn btn-info mt-2">Leer</a>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <img src="./img/portadaREYNAROJA.jpg" alt="Portada del libro 2" class="card-img-top rounded">
                        <a href="" class="btn btn-info mt-2">Leer</a>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <img src="./img/portadaSELECCION.jpg" alt="Portada del libro 3" class="card-img-top rounded">
                        <a href="" class="btn btn-info mt-2">Leer</a>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <img src="./img/portadaTODOLOQUENUNCAFUIMOS.jpg" alt="Portada del libro"
                            class="card-img-top rounded">
                        <a href="" class="btn btn-info mt-2">Leer</a>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <img src="./img/portadaAGUACHOCOLATE.jpg" alt="Portada del libro 2"
                            class="card-img-top rounded">
                        <a href="" class="btn btn-info mt-2">Leer</a>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <img src="./img/portadaORGULLOPREJUICIO.jpg" alt="Portada del libro 3"
                            class="card-img-top rounded">
                        <a href="" class="btn btn-info mt-2">Leer</a>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</body>

</html>