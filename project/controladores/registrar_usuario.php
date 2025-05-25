<?php
/**
 * Controlador para registrar nuevos usuarios
 */
session_start();
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $rol = isset($_POST['rol']) ? $_POST['rol'] : '';
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    
    // Validaciones básicas
    if (empty($nombre) || empty($usuario) || empty($password) || empty($rol) || empty($correo)) {
        header("Location: ../registro.php?error=1");
        exit;
    }
    
    try {
        // Verificar si el usuario ya existe
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        
        if ($stmt->fetch()) {
            header("Location: ../registro.php?error=2");
            exit;
        }
        
        // Crear el hash de la contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertar nuevo usuario
        $stmt = $conexion->prepare("
            INSERT INTO usuarios (nombre, usuario, password, rol, correo) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$nombre, $usuario, $password_hash, $rol, $correo]);
        
        header("Location: ../index.php?registro=1");
        exit;
        
    } catch (PDOException $e) {
        error_log("Error al registrar usuario: " . $e->getMessage());
        header("Location: ../registro.php?error=3");
        exit;
    }
} else {
    header("Location: ../registro.php");
    exit;
}