<?php
/**
 * Controlador para gestión de productos
 */
session_start();
require_once '../config/conexion.php';
require_once '../modelos/Producto.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit;
}

$productoModel = new Producto($conexion);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';
    
    switch ($accion) {
        case 'agregar':
            $datos = [
                'codigo' => $_POST['codigo'],
                'nombre' => $_POST['nombre'],
                'descripcion' => $_POST['descripcion'],
                'id_categoria' => $_POST['categoria'],
                'id_unidad_medida' => $_POST['unidad_medida'],
                'precio_compra' => $_POST['precio_compra'],
                'precio_venta' => $_POST['precio_venta'],
                'stock' => $_POST['stock'],
                'stock_minimo' => $_POST['stock_minimo'],
                'afecto_igv' => isset($_POST['afecto_igv']) ? 1 : 0,
                'codigo_sunat' => $_POST['codigo_sunat']
            ];
            
            if ($productoModel->agregar($datos)) {
                header("Location: ../productos.php?success=1");
            } else {
                header("Location: ../productos.php?error=1");
            }
            break;
            
        case 'editar':
            $id = $_POST['id'];
            $datos = [
                'codigo' => $_POST['codigo'],
                'nombre' => $_POST['nombre'],
                'descripcion' => $_POST['descripcion'],
                'id_categoria' => $_POST['categoria'],
                'id_unidad_medida' => $_POST['unidad_medida'],
                'precio_compra' => $_POST['precio_compra'],
                'precio_venta' => $_POST['precio_venta'],
                'stock_minimo' => $_POST['stock_minimo'],
                'afecto_igv' => isset($_POST['afecto_igv']) ? 1 : 0,
                'codigo_sunat' => $_POST['codigo_sunat']
            ];
            
            if ($productoModel->actualizar($id, $datos)) {
                header("Location: ../productos.php?success=2");
            } else {
                header("Location: ../productos.php?error=2");
            }
            break;
            
        case 'eliminar':
            $id = $_POST['id'];
            if ($productoModel->eliminar($id)) {
                header("Location: ../productos.php?success=3");
            } else {
                header("Location: ../productos.php?error=3");
            }
            break;
            
        case 'ajustar_stock':
            $id = $_POST['id'];
            $cantidad = $_POST['cantidad'];
            $tipo = $_POST['tipo_movimiento'];
            
            if ($productoModel->actualizarStock($id, $cantidad, $tipo)) {
                header("Location: ../productos.php?success=4");
            } else {
                header("Location: ../productos.php?error=4");
            }
            break;
    }
    
    exit;
}

// Si no es POST, redirigir
header("Location: ../productos.php");
exit;