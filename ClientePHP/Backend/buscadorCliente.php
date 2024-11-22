<?php
session_start(); // Inicia la sesión para almacenar los datos
require_once '../vendor/autoload.php';
require_once __DIR__ . '/../Clases/MyFirebase.php';

use Clases\MyFirebase\MyFirebase;

$firebase = new MyFirebase("suscripciondigital-2ad4a");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Limpiar y validar la entrada
    $_titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $_categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : '';

    // Verificar si la categoría está vacía
    if ($_categoria === "") {
        $respuesta = $firebase->getRespuesta(300);
        $_SESSION['mensaje'] = $respuesta . "\n";
        header("Location: ../index.php");
        exit;
    }

    try {
        // Buscar el título
        $result = $firebase->getProducto($_categoria, $_titulo);

        if ($result) {
            // Producto encontrado
            $respuesta = $firebase->getRespuesta(201);
            $isbn = $result["ISBN"];
            $titulo = $result["Titulo"];

            // Guardar detalles en la sesión
            $_SESSION['mensaje'] = $respuesta . "\n";
            $_SESSION['ISBN'] = $isbn;
            $_SESSION['titulo'] = $titulo;
            $_SESSION['portada'] = $firebase->getPortada($isbn);
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
    header("Location: ../index.php");
    exit;
}
