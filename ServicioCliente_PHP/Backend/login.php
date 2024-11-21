<?php
session_start(); // Iniciar la sesión
require_once '../vendor/autoload.php';

require_once __DIR__ . '/../Clases/MyFirebase.php';
use \Clases\MyFirebase\MyFirebase;

$firebase = new MyFirebase("suscripciondigital-2ad4a");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Llamada al método loginUser
    $result = $firebase->loginUser($username, $password);

    if ($result) {
        // Guardar nombre de usuario en la sesión
        $_SESSION['username'] = $result['username'];

        echo "<script>
                alert('Inicio de sesión exitoso. Bienvenido, " . $result['username'] . "');
                window.location.href = '../usuario.php';
            </script>";

    } else {
        // Error en el inicio de sesión
        echo "<script>
                alert('Error: Usuario o contraseña incorrectos.');
                window.location.href = '../login.html';
              </script>";
    }

}
