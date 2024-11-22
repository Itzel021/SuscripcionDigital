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

        // Configurar el método HTTP y los datos 
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
    //Función para obtener todos los usuarios
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

        // Crear el array de datos con los campos
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

        // Verificar si el usuario existe
        if ($response) {
            foreach ($response as $userId => $user) {
                // Comparar los datos
                if (
                    isset($user['username'], $user['password']) &&
                    $user['username'] === $username &&
                    password_verify($password, $user['password']) // Validar la contraseña encriptada
                ) {
                    // Añadir el ID de usuario a los datos y regresar los datos
                    $user['id'] = $userId;
                    return $user;
                }
            }
        }
        // Retornar false si el usuario no existe o la contraseña no es válida
        return false;
    }

    //Función para buscar el mensaje de un codigo
    public function getRespuesta($code)
    {
        $url = $this->UrlFirebase . "respuestas/{$code}.json";
        $response = $this->runCurl($url, 'GET');

        if ($response !== null) {
            return $response;
        }
        return "Código no disponible";
    }

    //Funcion para obtener la url de la portada
    public function getPortada($isbn)
    {
        $url = $this->UrlFirebase . "detalles/{$isbn}/Portada.json";
        $response = $this->runCurl($url, 'GET');

        if ($response && !empty($response)) {
            return $response;
        }
        return "Portada no disponible";
    }

    //Funcion para buscar un producto
    public function getProducto($categoria, $titulo)
    {
        $url = $this->UrlFirebase . "productos/{$categoria}.json";
        $productos = $this->runCurl($url, 'GET');

        // Validar que existen productos en la categoría
        if ($productos && is_array($productos)) {
            foreach ($productos as $isbn => $nombre) {
                // Comparar el título ignorando mayúsculas y espacios
                if (strtolower(trim($nombre)) === strtolower(trim($titulo))) {
                    return [
                        "ISBN" => $isbn,
                        "Titulo" => $nombre
                    ];
                }
            }
        }
    }

    //Función para obtener los detalles de un producto
    public function getDetalles($isbn)
    {
        $url = $this->UrlFirebase . "detalles/{$isbn}.json";
        $response = $this->runCurl($url, 'GET');
        if ($response && !empty($response)) {
            return $response;
        }
    }

    //Función para obtener todos los productos
    public function getAllProductos()
    {
        $url = $this->UrlFirebase . "detalles.json";
        $response = $this->runCurl($url, 'GET');
        if ($response && !empty($response)) {
            return $response;
        }
    }
}
