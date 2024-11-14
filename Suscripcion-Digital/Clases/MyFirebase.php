<?php
namespace Clases\MyFirebase;

class MyFirebase
{
    private $UrlFirebase;

    public function __construct($project)
    {
        $this->UrlFirebase = "https://{$project}-default-rtdb.firebaseio.com/";
    }
    
    private function runCurl($url, $method, $data = null)
    {
        // Inicializar cURL
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // Configurar el método HTTP y los datos si es POST
        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }

        // Ejecutar la solicitud y cerrar cURL
        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true); // Decodificar la respuesta JSON
    }
    public function getAllUsers()
    {
        $url = $this->UrlFirebase . "usuarios.json";
        return $this->runCurl($url, 'GET');
    }

    //Función para registrar usuarios
    public function registerUser($username, $email, $password, $plan, $pago, $numCuenta)
    {
        // Encriptar la contraseña
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
        // Crear el array de datos con los nuevos campos
        $Data = [
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'plan' => $plan,
            'pago' => $pago,
            'numCuenta' => $numCuenta
        ];
    
        $url = $this->UrlFirebase . "usuarios.json";
        $response = $this->runCurl($url, 'POST', $Data);
    
        // Verificar si la respuesta es exitosa
        if ($response && isset($response['name'])) {
            return $Data; // Devuelve los datos del usuario registrado
        } else {
            // El registro falló
            return false;
        }
    }

    //Función para iniciar sesión
    public function loginUser($username, $password)
{
    $url = $this->UrlFirebase . "usuarios.json";
    $response = $this->runCurl($url, 'GET');
    
    // Verificar si hay respuesta de Firebase y el usuario existe
    if ($response) {
        foreach ($response as $userId => $user) {
            // Verificar si los campos requeridos existen en el usuario y el nombre de usuario coincide
            if (
                isset($user['username'], $user['password']) &&
                $user['username'] === $username &&
                password_verify($password, $user['password']) // Validar la contraseña encriptada
            ) {
                // Añadir el ID de usuario a los datos y retornar todos los datos del usuario
                $user['id'] = $userId;
                return $user; 
            }
        }
    }
    // Retornar false si el usuario no existe o la contraseña no es válida
    return false;
}
    
}
