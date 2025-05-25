<?php
/**
 * Controlador de autenticación
 */
session_start();
require_once '../config/conexion.php';

// Si ya está autenticado, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

// Validar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validaciones básicas
    if (empty($usuario) || empty($password)) {
        header("Location: ../index.php?error=1");
        exit;
    }
    
    try {
        // Consultar usuario
        $stmt = $conexion->prepare("
            SELECT id, nombre, usuario, password, rol, correo, estado 
            FROM usuarios 
            WHERE usuario = ? AND estado = 1
        ");
        $stmt->execute([$usuario]);
        
        if ($usuario_data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Verificar contraseña
            if (password_verify($password, $usuario_data['password'])) {
                // Registrar último login
                $stmt = $conexion->prepare("
                    UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?
                ");
                $stmt->execute([$usuario_data['id']]);
                
                // Guardar datos en sesión
                $_SESSION['usuario_id'] = $usuario_data['id'];
                $_SESSION['usuario_nombre'] = $usuario_data['nombre'];
                $_SESSION['usuario_usuario'] = $usuario_data['usuario'];
                $_SESSION['usuario_rol'] = $usuario_data['rol'];
                $_SESSION['usuario_correo'] = $usuario_data['correo'];
                
                // Redirigir al dashboard
                header("Location: ../dashboard.php");
                exit;
            }
        }
        
        // Si llegamos aquí es porque las credenciales son incorrectas
        header("Location: ../index.php?error=1");
        exit;
        
    } catch (PDOException $e) {
        // Log del error
        error_log("Error de autenticación: " . $e->getMessage());
        header("Location: ../index.php?error=2");
        exit;
    }
} else {
    // Si no es POST, redirigir al inicio
    header("Location: ../index.php");
    exit;
}