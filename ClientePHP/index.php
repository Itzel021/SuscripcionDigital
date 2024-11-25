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

        .card {
            width: 400px;
        }

        .card img {
            width: 400px;
            height: 500px;
        }
    </style>
</head>

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
                <form id="buscarForm" onsubmit="event.preventDefault(); buscarProducto();">
                    <label for="categoria" class="me-2"><i class="fas fa-list"></i> Selecciona una Categoría:</label>
                    <div class="form-group">
                        <select id="categoria" name="categoria" class="form-control" required>
                            <option value="">Categorías disponibles</option>
                            <option value="libros">Libros</option>
                            <option value="revistas">Revistas</option>
                            <option value="mangas">Mangas</option>
                        </select>
                    </div>
                    <label for="titulo" class="me-2"><i class="fas fa-search"></i> Título:</label>
                    <div class="form-group d-flex align-items-center">
                        <input type="text" id="titulo" name="titulo" placeholder="escribe el título..."
                            class="form-control me-2" required>
                        <button class="btn btn-success" type="submit">BUSCAR</button>
                    </div>
                </form>
                <div id="message" class="alert d-none" role="alert"></div>
            </div>
            <div class="col-md-6 mx-auto text-center">
                <div class="card">
                    <img src="./img/default.jpg" class="card-img-top" alt="Portada" id="cardImage">
                    <div class="mt-2">
                        <h5 class="card-title" id="cardTitle"></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function buscarProducto() {
            const titulo = document.getElementById('titulo').value;
            const categoria = document.getElementById('categoria').value;
            const messageDiv = document.getElementById('message');
            const cardImage = document.getElementById('cardImage');
            const cardTitle = document.getElementById('cardTitle');

            // Mostrar mensaje de carga
            messageDiv.className = 'alert alert-info';
            messageDiv.textContent = 'Buscando...';
            messageDiv.classList.remove('d-none');

            fetch('/ws/SuscripcionDigital/ClientePHP/titulos?titulo=' + encodeURIComponent(titulo) + '&categoria=' + encodeURIComponent(categoria))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la búsqueda');
                    }
                    return response.json();
                })
                .then(data => {
                    // Actualizar el mensaje y la imagen de la portada
                    const messageDiv = document.getElementById('message');
                    const cardImage = document.getElementById('cardImage');
                    const cardTitle = document.getElementById('cardTitle');

                    messageDiv.classList.remove('d-none', 'alert-success', 'alert-danger');
                    cardImage.src = './img/default.jpg';  // Restaurar imagen predeterminada
                    cardTitle.textContent = '';  // Limpiar título anterior

                    if (data.data) {
                        cardImage.src = './img/' + data.data.Portada + '.jpg';  // Asignar la portada
                        cardTitle.textContent = `${data.data.ISBN} : ${data.data.Titulo}`;  // Asignar el título
                        messageDiv.classList.add('alert-success');
                        messageDiv.textContent = data.message;  // Mostrar mensaje de éxito
                    } else {
                        messageDiv.classList.add('alert-danger');
                        messageDiv.textContent = data.error || '305 : Título no disponible';  // Mensaje de error
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const messageDiv = document.getElementById('message');
                    messageDiv.classList.remove('d-none', 'alert-success');
                    messageDiv.classList.add('alert-danger');
                    messageDiv.textContent = '305 : Título no disponible';
                });
        }
    </script>
</body>

</html>