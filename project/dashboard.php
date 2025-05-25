<?php
session_start();
require_once 'config/conexion.php';
require_once 'modelos/Dashboard.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$dashboard = new Dashboard($conexion);
$estadisticas = $dashboard->obtenerEstadisticas();
$ventasRecientes = $dashboard->obtenerVentasRecientes(5);
$productosPocoStock = $dashboard->obtenerProductosPocoStock(5);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Ventas e Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
    <?php include 'includes/menu.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-download me-1"></i>Exportar
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-calendar3 me-1"></i>Hoy
                        </button>
                    </div>
                </div>

                <!-- Tarjetas de estadísticas -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Ventas (Mes Actual)</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">S/. <?php echo number_format($estadisticas['ventasMes'], 2); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-cart-check fs-1 text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Productos</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $estadisticas['totalProductos']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-box-seam fs-1 text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Clientes</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $estadisticas['totalClientes']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-people fs-1 text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Alertas Stock</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $estadisticas['alertasStock']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-exclamation-triangle fs-1 text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfico y tabla de productos con poco stock -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Resumen de Ventas</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-area">
                                    <canvas id="myAreaChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Productos con Poco Stock</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Stock</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($productosPocoStock as $producto): ?>
                                        <tr>
                                            <td><?php echo $producto['nombre']; ?></td>
                                            <td><?php echo $producto['stock']; ?></td>
                                            <td>
                                                <?php if ($producto['stock'] <= 5): ?>
                                                <span class="badge bg-danger">Crítico</span>
                                                <?php else: ?>
                                                <span class="badge bg-warning">Bajo</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <a href="productos.php?filtro=bajo_stock" class="btn btn-sm btn-primary d-block">Ver todos</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Últimas ventas -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Ventas Recientes</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Comprobante</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ventasRecientes as $venta): ?>
                                    <tr>
                                        <td><?php echo $venta['tipo_comprobante'] . ' ' . $venta['numero']; ?></td>
                                        <td><?php echo $venta['cliente']; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></td>
                                        <td>S/. <?php echo number_format($venta['total'], 2); ?></td>
                                        <td>
                                            <?php if ($venta['estado'] == 'Completada'): ?>
                                            <span class="badge bg-success">Completada</span>
                                            <?php elseif ($venta['estado'] == 'Anulada'): ?>
                                            <span class="badge bg-danger">Anulada</span>
                                            <?php else: ?>
                                            <span class="badge bg-warning">Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="ver_venta.php?id=<?php echo $venta['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="comprobante.php?id=<?php echo $venta['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-file-pdf"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="ventas.php" class="btn btn-sm btn-primary">Ver todas las ventas</a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>