<?php
session_start(); // Iniciar la sesión
require_once '../vendor/autoload.php';
require_once __DIR__ . '/../Clases/MyFirebase.php';

use Clases\MyFirebase\MyFirebase;

$firebase = new MyFirebase("suscripciondigital-2ad4a");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Limpiar y validar la entrada
    $_titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $_categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : '';

    // Validar que la categoría no sea nula o vacía
    if ($_categoria === "") {
        $respuesta = $firebase->getRespuesta(300);
        $_SESSION['mensaje'] = $respuesta . "\n";
        header("Location: ../buscar.php");
        exit;
    }

    try {
        $result = $firebase->getProducto($_categoria, $_titulo);
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

            $producto[] = [
                'ISBN' => $isbn,
                'Titulo' => $titulo,
                'Portada' => $detalles['Portada']
            ];
            
            // Guardar en la sesión
            $_SESSION['mensaje'] = $respuesta . "\n";
            $_SESSION['producto'] = $producto;
            $_SESSION['detalles'] = $detallesPorISBN;

        } else {
            // Producto no encontrado
            $respuesta = $firebase->getRespuesta(305);
            $_SESSION['mensaje'] = $respuesta;
        }

    } catch (Exception $e) {
        // Error genérico
        $respuesta = $firebase->getRespuesta(999);
        $_SESSION['mensaje'] = $respuesta . $e->getMessage();
    }

    // Redirigir de vuelta al formulario
    header("Location: ../buscar.php");
    exit;
}
