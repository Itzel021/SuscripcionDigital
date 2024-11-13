<?php
require_once '../vendor/autoload.php';
require_once __DIR__ . '/../Clases/MyFirebase.php';
use Clases\MyFirebase\MyFirebase;

$firebase = new MyFirebase("suscripciondigital-2ad4a");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $plan = $_POST['plan'];
    $pago = $_POST['pago'];
    $numCuenta = $_POST['numCuenta'];

    
    // Registrar el usuario en Firebase
    $result = $firebase->registerUser($username, $email, $password, $plan, $pago, $numCuenta);

    if ($result) {
        // Registro exitoso
        echo "<script>
                alert('Registro exitoso. Por favor inicia sesión.');
                window.location.href = '../login.html';
              </script>";
    } else {
        // Error al registrarse
        echo "<script>
                alert('Error: usuario no registrado.');
                window.location.href = '../registro.html';
              </script>";
    }      
}
?>
