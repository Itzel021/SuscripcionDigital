<?php
require './vendor/autoload.php';
require_once './Clases/MyFirebase.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Clases\MyFirebase\MyFirebase;


$app = AppFactory::create();
$firebase = new MyFirebase("suscripciondigital-2ad4a");

$app->setBasePath('/ws/SuscripcionDigital/ClientePHP');

// Ruta para manejar las suscripciones
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
                $response->getBody()->write(json_encode(['error' =>'401 : ' . $respuesta]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }
    } 
    
    // Registrar usuario
    $result = $firebase->registerUser($username, $email, $password, $plan, $pago, $numCuenta);

    if ($result) {
        $respuesta = $firebase->getRespuesta(205); // Suscripción exitosa
        $response->getBody()->write(json_encode(['message' => '205 : '. $respuesta]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } else {
        $respuesta = $firebase->getRespuesta(502); // Error al registrar usuario
        $response->getBody()->write(json_encode(['error' => '502 : ' . $respuesta]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Ruta para obtener todos los productos
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

// Ruta para buscar titulos
$app->post('/titulos', function (Request $request, Response $response) use ($firebase) {
    // Obtener los parámetros de la solicitud
    $params = json_decode($request->getBody(), true);
    $titulo = trim($params['titulo'] ?? '');
    $categoria = trim($params['categoria'] ?? '');

    // Validar la entrada
    if (empty($categoria)) {
        $respuesta = $firebase->getRespuesta(300); //Categoria no encontrada
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
            $respuesta = $firebase->getRespuesta(305); //Titulo no disponible
            $response->getBody()->write(json_encode(['error' => '305 : ' . $respuesta]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    } catch (Exception $e) {
        // Manejo de errores
        $response->getBody()->write(json_encode(['error' => 'Error al buscar el producto: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Ejecutar la aplicación
$app->run();
