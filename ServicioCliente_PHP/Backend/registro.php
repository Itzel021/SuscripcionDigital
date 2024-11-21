<?php
require_once '../vendor/autoload.php';
require_once __DIR__ . '/../Clases/MyFirebase.php';
use Clases\MyFirebase\MyFirebase;

$firebase = new MyFirebase("suscripciondigital-2ad4a");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Datos enviados desde el formulario
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $plan = trim($_POST['plan']);
    $pago = trim($_POST['pago']);
    $numCuenta = trim($_POST['numCuenta']);

    // Validación de campos vacíos
    if (empty($username) || empty($email) || empty($password) || empty($plan) || empty($pago) || empty($numCuenta)) {
        echo "<script>
                alert('Error: Todos los campos son obligatorios.');
                window.location.href = '../registro.html';
              </script>";
        exit;
    }

    // Validación del formato de correo electrónico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>
                alert('Error: El correo electrónico no es válido.');
                window.location.href = '../registro.html';
              </script>";
        exit;
    }

    // Validación del formato del número de cuenta 
    if (!preg_match("/^\d{10}$/", $numCuenta)) {
        echo "<script>
                alert('Error: El número de cuenta debe tener 10 dígitos.');
                window.location.href = '../registro.html';
              </script>";
        exit;
    }

    // Verificación de si el correo ya está registrado
    $existingUsers = $firebase->getAllUsers();
    if ($existingUsers) {
        foreach ($existingUsers as $user) {
            if (isset($user['email']) && $user['email'] === $email) {
                echo "<script>
                        alert('Error: El correo electrónico ya está registrado.');
                        window.location.href = '../registro.html';
                      </script>";
                exit;
            }
            // Validar que el nombre de usuario no esté registrado
            if (isset($user['username']) && $user['username'] === $username) {
                echo "<script>
                        alert('Error: El nombre de usuario ya está en uso.');
                        window.location.href = '../registro.html';
                      </script>";
                exit;
            }
        }
    }

    // Si pasa todas las validaciones, registrar el usuario
    $result = $firebase->registerUser($username, $email, $password, $plan, $pago, $numCuenta);

    if ($result) {
        echo "<script>
                alert('Registro exitoso. Por favor inicia sesión.');
                window.location.href = '../login.html';
              </script>";
    } else {
        echo "<script>
                alert('Error: usuario no registrado.');
                window.location.href = '../registro.html';
              </script>";
    }
}
?>
