<?php
// Obtener la URL del PDF desde la URL del parámetro
$pdf_url = isset($_GET['url']) ? $_GET['url'] : '';

// Verificar si la URL del PDF es válida
if (empty($pdf_url)) {
    echo "No se ha proporcionado un archivo PDF válido.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contenido del PDF</title>
    <!-- Agregar enlace a Bootstrap -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #8b1865;
        }
        .iframe-container {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 8px;
        }

        .btn-back {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-1">
                <a href="javascript:history.back()" class="btn btn-info btn-back mr-4">Regresar</a>
            </div>
            <div class="col-10">
                <div class="iframe-container mt-2">
                    <iframe src="<?php echo htmlspecialchars($pdf_url); ?>" width="100%" height="600px"
                        frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>