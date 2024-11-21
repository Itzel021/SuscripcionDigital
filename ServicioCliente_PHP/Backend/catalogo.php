<?php
session_start();
require_once '../vendor/autoload.php';
require_once __DIR__ . '/../Clases/MyFirebase.php';

use \Clases\MyFirebase\MyFirebase;

$firebase = new MyFirebase("suscripciondigital-2ad4a");

if ($_SERVER["REQUEST_METHOD"] === "POST" || empty($_SESSION['detalles'])) {
    try {
        $result = $firebase->getAllProductos(); // Obtener todos los productos
        if ($result) {
            $detallesPorISBN = [];

            foreach ($result as $isbn => $productoData) {
                $detalles = $firebase->getDetalles($isbn); // Obtener detalles del producto
                if ($detalles) {
                    $detallesPorISBN[$isbn] = $detalles; // Guardar en detalles
                }
            }
            $_SESSION['detalles'] = $detallesPorISBN;
            // Redirigir a la página de usuario para mostrar los productos
            header('Location: ../usuario.php');
            exit();
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
