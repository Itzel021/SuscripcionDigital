<?php
require './vendor/autoload.php';
require_once './Clases/MyFirebase.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Clases\MyFirebase\MyFirebase;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$app = AppFactory::create();
$firebase = new MyFirebase("suscripciondigital-2ad4a");
$app->setBasePath('/ws/SuscripcionDigital/ClientePHP');

//Endpoint para registrar los suscriptores
$app->post('/suscriptores', function (Request $request, Response $response) use ($firebase) {
    $params = json_decode($request->getBody(), true);


    $username = trim($params['username'] ?? '');
    $email = trim($params['email'] ?? '');
    $password = trim($params['password'] ?? '');
    $plan = trim($params['plan'] ?? '');
    $pago = trim($params['pago'] ?? '');
    $numCuenta = trim($params['numCuenta'] ?? '');

    // Validaciones
    if (empty($username) || empty($email) || empty($password) || empty($plan) || empty($pago) || empty($numCuenta)) {
        $response->getBody()->write(json_encode(['error' => 'Todos los campos son obligatorios.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response->getBody()->write(json_encode(['error' => 'El correo electronico no es válido.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    if (!preg_match("/^\d{10}$/", $numCuenta)) {
        $response->getBody()->write(json_encode(['error' => 'El numero de cuenta debe tener 10 digitos.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Validar si ya existe el correo o usuario
    $existingUsers = $firebase->getAllUsers();
    if ($existingUsers) {
        foreach ($existingUsers as $user) {

            if (isset($user['email']) && $user['email'] === $email) {
                $respuesta = $firebase->getRespuesta(402); // El correo ya está registrado
                $response->getBody()->write(json_encode(['error' => '402 : ' . $respuesta]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            if (isset($user['username']) && $user['username'] === $username) {
                $respuesta = $firebase->getRespuesta(401); // El nombre de usuario ya está en uso
                $response->getBody()->write(json_encode(['error' => '401 : ' . $respuesta]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }
    }

    // Registrar usuario
    $result = $firebase->registerUser($username, $email, $password, $plan, $pago, $numCuenta);

    if ($result) {
        $respuesta = $firebase->getRespuesta(205); // Suscripción exitosa
        $response->getBody()->write(json_encode(['message' => '205 : ' . $respuesta]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } else {
        $respuesta = $firebase->getRespuesta(502); // Error al registrar usuario
        $response->getBody()->write(json_encode(['error' => '502 : ' . $respuesta]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

//Endpoint para ver el catálogo de todos los productos
$app->get('/productos', function (Request $request, Response $response) use ($firebase) {
    try {
        // Obtener todos los productos
        $result = $firebase->getAllProductos();

        if ($result) {
            $detallesPorISBN = [];

            foreach ($result as $isbn => $productoData) {
                // Obtener los detalles del producto usando el ISBN
                $detalles = $firebase->getDetalles($isbn);
                if ($detalles) {
                    $detallesPorISBN[$isbn] = $detalles;
                }
            }

            // Respuesta JSON con todos los productos
            $response->getBody()->write(json_encode($detallesPorISBN, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            // Si no hay productos
            $response->getBody()->write(json_encode(['error' => 'No se encontraron productos.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    } catch (Exception $e) {
        // Manejo de errores
        $response->getBody()->write(json_encode(['error' => 'Error al obtener los productos: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

//Endpoint para buscar titulos (usuario no suscrito)
$app->get('/titulos', function (Request $request, Response $response) use ($firebase) {
    // Obtener los parámetros de consulta
    $queryParams = $request->getQueryParams();
    $titulo = trim($queryParams['titulo'] ?? '');
    $categoria = trim($queryParams['categoria'] ?? '');

    // Validar la entrada
    if (empty($categoria)) {
        $respuesta = $firebase->getRespuesta(300); // Categoría no encontrada
        $response->getBody()->write(json_encode(['error' => '300 : ' . $respuesta]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    try {
        // Buscar el producto
        $result = $firebase->getProducto($categoria, $titulo);
        if ($result) {
            // Obtener la portada utilizando el ISBN
            $portada = $firebase->getPortada($result['ISBN']);
            $respuesta = $firebase->getRespuesta(201); // Título encontrado exitosamente

            // Agregar la portada a la respuesta
            $response->getBody()->write(json_encode([
                'message' => '201 : ' . $respuesta,
                'data' => [
                    'ISBN' => $result['ISBN'],
                    'Titulo' => $result['Titulo'],
                    'Portada' => $portada
                ]
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $respuesta = $firebase->getRespuesta(305); // Título no disponible
            $response->getBody()->write(json_encode(['error' => '305 : ' . $respuesta]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    } catch (Exception $e) {
        // Manejo de errores
        $response->getBody()->write(json_encode(['error' => 'Error al buscar el producto: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

//Endpoint para buscar los detalles de un titulo (Usuario suscrito)
$app->get('/detalles', function ($request, $response, $args) use ($firebase) {
    $params = $request->getQueryParams(); // Obtener parámetros de consulta

    $titulo = isset($params['titulo']) ? trim($params['titulo']) : '';
    $categoria = isset($params['categoria']) ? trim($params['categoria']) : '';

    // Validar la entrada
    if (empty($categoria)) {
        $respuesta = $firebase->getRespuesta(300); // Categoría no encontrada
        $response->getBody()->write(json_encode(['error' => '300 : ' . $respuesta]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    try {
        $result = $firebase->getProducto($categoria, $titulo);
        if ($result) {
            // Producto encontrado
            $respuesta = $firebase->getRespuesta(201);
            $isbn = $result["ISBN"];
            $titulo = $result["Titulo"];

            $productos = [];
            $detallesPorISBN = [];
            $detalles = $firebase->getDetalles($isbn);

            if ($detalles) {
                $detallesPorISBN[$isbn] = $detalles;
            }

            $productos[] = [
                'ISBN' => $isbn,
                'Titulo' => $titulo,
                'Portada' => $detalles['Portada']
            ];

            // Respuesta exitosa con detalles del producto
            $response->getBody()->write(json_encode([
                'message' => '201 :' . $respuesta,
                'producto' => $productos,
                'detalles' => $detallesPorISBN
            ]));
            return $response->withStatus(200);
        } else {
            $respuesta = $firebase->getRespuesta(305); // Título no encontrado
            $response->getBody()->write(json_encode(['message' => '305 :' . $respuesta]));
            return $response->withStatus(404); 
        }
    } catch (Exception $e) {
        // Error genérico
        $respuesta = $firebase->getRespuesta(999);
        $response->getBody()->write(json_encode(['message' => $respuesta . $e->getMessage()]));
        return $response->withStatus(500);
    }
});


//Endpoint para escuchar el webhook
$app->post('/webhooks/productos/add', function (Request $request, Response $response) use ($firebase) {
    // Decodificar el cuerpo de la solicitud
    $data = json_decode($request->getBody(), true);

    // Validar el evento
    if (empty($data['evento']) || $data['evento'] !== 'nuevo_producto') {
        $response->getBody()->write(json_encode(["mensaje" => "Evento no reconocido"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Validar los datos del producto
    $producto = $data['producto'] ?? null;
    if (!$producto || empty($producto['Nombre']) || empty($producto['Autor']) || empty($producto['Precio']) || empty($producto['Categoria'])) {
        $response->getBody()->write(json_encode(["mensaje" => "Datos del producto inválidos"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    try {
        // Obtener los correos electrónicos directamente desde Firebase
        $correos = $firebase->getReference("usuarios", true);

        // Verificar si se encontraron correos
        if (empty($correos)) {
            $response->getBody()->write(json_encode(["mensaje" => "No se encontraron correos electrónicos"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Preparar el mensaje del correo
        $asunto = "Nuevo producto disponible: " . $producto['Nombre'];
        $mensaje = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Nuevo Producto Disponible</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    background-color: #f4f4f4; 
                    margin: 0; 
                    padding: 20px; 
                    text-align: center; 
                }
                .container { 
                    background-color: #ffffff; 
                    border-radius: 5px; 
                    padding: 20px; 
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
                    max-width: 600px; 
                    margin: auto; 
                }
                h1 { 
                    color: #333; 
                    margin-bottom: 20px; 
                }
                .product-info { 
                    background-color: rgba(167, 97, 167, 0.6);
                    padding: 15px; 
                    border-radius: 8px; 
                    margin: 20px 0; 
                    text-align: left; 
                }
                .footer {color: #8b1865}
            </style>
        </head>
        <body>
            <div class='container'>
                <h1 class='footer'>¡Nuevo Producto Disponible! 🎉</h1>
                <p>Estamos emocionados de anunciar que se ha agregado un nuevo producto a nuestra colección 🛍️ :</p>
                <div class='product-info'>
                    <strong>📚 Nombre:</strong> {$producto['Nombre']}<br>
                    <strong>✍️ Autor:</strong> {$producto['Autor']}<br>
                    <strong>💲 Precio:</strong> {$producto['Precio']}<br>
                    <p>🔍 Búscala en la categoría: <strong>{$producto['Categoria']}</strong></p>
                </div>
                <div>
                    <p>¡Visítanos para más detalles y no te pierdas nuestras ofertas! 🏷️</p>
                    <h3 class='footer'>El equipo de Triple Request 🤗 </h3>
                </div>
            </div>
        </body>
        </html>
        ";

        // Configurar PHPMailer
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'triplerequest21@gmail.com';
        $mail->Password = 'mcnm hsew bisi scjg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        //Remitente
        $mail->setFrom('triplerequest21@gmail.com', 'Triple Request');

        // Enviar correos a todos los usuarios
        foreach ($correos as $email) {
            $mail->addAddress($email);
            $mail->Subject = $asunto;
            $mail->isHTML(true);
            $mail->Body = $mensaje;

            try {
                $mail->send();
            } catch (Exception $e) {
                // Registrar el error sin interrumpir el envío de otros correos
                error_log("Error enviando a {$email}: {$mail->ErrorInfo}");
            }

            // Limpiar destinatarios para el próximo correo
            $mail->clearAddresses();
        }

        // Responder éxito
        $response->getBody()->write(json_encode(["mensaje" => "Notificaciones enviadas correctamente"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (Exception $e) {
        // Manejar errores
        $response->getBody()->write(json_encode([
            "mensaje" => "Ocurrió un error al enviar notificaciones",
            "error" => $e->getMessage()
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Ejecutar la aplicación
$app->run();
