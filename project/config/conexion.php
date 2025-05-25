<?php
/**
 * Configuración de conexión a la base de datos
 */

$host = "localhost";
$usuario = "root";
$password = "";
$base_datos = "sistema_ventas_sunat";

try {
    $conexion = new PDO("mysql:host=$host;dbname=$base_datos;charset=utf8", $usuario, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    exit;
}

// Configuración global
$config = [
    'empresa' => [
        'nombre' => 'Mi Empresa S.A.C.',
        'ruc' => '20505687452',
        'direccion' => 'Av. Principal 123, Lima',
        'telefono' => '(01) 555-1234',
        'correo' => 'contacto@miempresa.com',
        'logo' => 'assets/img/logo.png'
    ],
    'sunat' => [
        'usuario_sol' => 'USUARIO1',
        'clave_sol' => '********',
        'certificado' => 'certificado.pem',
        'entorno' => 'beta' // beta o produccion
    ]
];