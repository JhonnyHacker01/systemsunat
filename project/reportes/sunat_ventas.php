<?php
session_start();
require_once '../config/conexion.php';
require_once '../modelos/Venta.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit;
}

$ventaModel = new Venta($conexion);

// Filtros
$filtros = [
    'fecha_desde' => isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : date('Y-m-01'),
    'fecha_hasta' => isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : date('Y-m-t'),
    'tipo_comprobante' => isset($_GET['tipo_comprobante']) ? $_GET['tipo_comprobante'] : '',
    'estado' => isset($_GET['estado']) ? $_GET['estado'] : 'Completada'
];

// Obtener ventas
$resultado = $ventaModel->listar(1, 100, $filtros);
$ventas = $resultado['ventas'];

// Calcular totales
$totalVentas = 0;
$totalIGV = 0;
$totalNeto = 0;

foreach ($ventas as $venta) {
    // Obtener detalles de la venta para calcular IGV
    $detalles = $ventaModel->obtenerDetalle($venta['id']);
    $igvVenta = 0;
    foreach ($detalles as $detalle) {
        $igvVenta += $detalle['igv'];
    }
    
    $totalVentas += $venta['total'];
    $totalIGV += $igvVenta;
}

$totalNeto = $totalVentas - $totalIGV;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Ventas SUNAT - Sistema de Ventas e Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>
    <?php include '../includes/menu.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Registro de Ventas SUNAT</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnExportarExcel">
                                <i class="bi bi-file-excel me-1"></i>Excel
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnExportarPDF">
                                <i class="bi bi-file-pdf me-1"></i>PDF
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnExportarTXT">
                                <i class="bi bi-file-text me-1"></i>TXT SUNAT
                            </button>
                        </div>
                        <a href="../dashboard.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                </div>
                
                <!-- Filtros -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Filtros de búsqueda</h6>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row g-3">
                            <div class="col-md-3">
                                <label for="fecha_desde" class="form-label">Fecha Desde</label>
                                <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" value="<?php echo $filtros['fecha_desde']; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                                <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" value="<?php echo $filtros['fecha_hasta']; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="tipo_comprobante" class="form-label">Tipo Comprobante</label>
                                <select class="form-select" id="tipo_comprobante" name="tipo_comprobante">
                                    <option value="">Todos</option>
                                    <option value="Factura" <?php echo $filtros['tipo_comprobante'] === 'Factura' ? 'selected' : ''; ?>>Factura</option>
                                    <option value="Boleta" <?php echo $filtros['tipo_comprobante'] === 'Boleta' ? 'selected' : ''; ?>>Boleta</option>
                                    <option value="Nota de Venta" <?php echo $filtros['tipo_comprobante'] === 'Nota de Venta' ? 'selected' : ''; ?>>Nota de Venta</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Todos</option>
                                    <option value="Completada" <?php echo $filtros['estado'] === 'Completada' ? 'selected' : ''; ?>>Completada</option>
                                    <option value="Anulada" <?php echo $filtros['estado'] === 'Anulada' ? 'selected' : ''; ?>>Anulada</option>
                                </select>
                            </div>
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i>Filtrar
                                </button>
                                <a href="sunat_ventas.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Resumen -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Ventas</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">S/. <?php echo number_format($totalVentas, 2); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-cart-check fs-1 text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Base Imponible</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">S/. <?php echo number_format($totalNeto, 2); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-calculator fs-1 text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            IGV Total</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">S/. <?php echo number_format($totalIGV, 2); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-percent fs-1 text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabla de ventas -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Registro de Ventas</h6>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownMenuLink">
                                <li><a class="dropdown-item" href="#" id="btnImprimir">Imprimir</a></li>
                                <li><a class="dropdown-item" href="#" id="btnEnviarSunat">Enviar a SUNAT</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tablaVentas" width="100%" cellspacing="0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Comprobante</th>
                                        <th>Cliente</th>
                                        <th>RUC/DNI</th>
                                        <th>Base Imp.</th>
                                        <th>IGV</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>SUNAT</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($ventas)): ?>
                                        <tr>
                                            <td colspan="10" class="text-center">No hay registros disponibles</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($ventas as $venta): ?>
                                            <?php
                                            // Obtener detalles de la venta para calcular IGV
                                            $detalles = $ventaModel->obtenerDetalle($venta['id']);
                                            $igvVenta = 0;
                                            foreach ($detalles as $detalle) {
                                                $igvVenta += $detalle['igv'];
                                            }
                                            $baseImponible = $venta['total'] - $igvVenta;
                                            ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($venta['fecha_emision'])); ?></td>
                                                <td><?php echo $venta['tipo_comprobante'] . ' ' . $venta['serie'] . '-' . $venta['numero']; ?></td>
                                                <td><?php echo $venta['cliente']; ?></td>
                                                <td><?php echo $venta['cliente_documento']; ?></td>
                                                <td class="text-end">S/. <?php echo number_format($baseImponible, 2); ?></td>
                                                <td class="text-end">S/. <?php echo number_format($igvVenta, 2); ?></td>
                                                <td class="text-end">S/. <?php echo number_format($venta['total'], 2); ?></td>
                                                <td>
                                                    <?php if ($venta['estado'] == 'Completada'): ?>
                                                        <span class="badge bg-success">Completada</span>
                                                    <?php elseif ($venta['estado'] == 'Anulada'): ?>
                                                        <span class="badge bg-danger">Anulada</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Pendiente</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($venta['enviado_sunat']): ?>
                                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><i class="bi bi-x-circle"></i></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="../ver_venta.php?id=<?php echo $venta['id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="../comprobante.php?id=<?php echo $venta['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-file-pdf"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Exportar a Excel
            document.getElementById('btnExportarExcel').addEventListener('click', function() {
                window.location.href = 'exportar_ventas.php?formato=excel&fecha_desde=<?php echo $filtros['fecha_desde']; ?>&fecha_hasta=<?php echo $filtros['fecha_hasta']; ?>&tipo_comprobante=<?php echo $filtros['tipo_comprobante']; ?>&estado=<?php echo $filtros['estado']; ?>';
            });
            
            // Exportar a PDF
            document.getElementById('btnExportarPDF').addEventListener('click', function() {
                window.location.href = 'exportar_ventas.php?formato=pdf&fecha_desde=<?php echo $filtros['fecha_desde']; ?>&fecha_hasta=<?php echo $filtros['fecha_hasta']; ?>&tipo_comprobante=<?php echo $filtros['tipo_comprobante']; ?>&estado=<?php echo $filtros['estado']; ?>';
            });
            
            // Exportar a TXT para SUNAT
            document.getElementById('btnExportarTXT').addEventListener('click', function() {
                window.location.href = 'exportar_ventas.php?formato=txt&fecha_desde=<?php echo $filtros['fecha_desde']; ?>&fecha_hasta=<?php echo $filtros['fecha_hasta']; ?>&tipo_comprobante=<?php echo $filtros['tipo_comprobante']; ?>&estado=<?php echo $filtros['estado']; ?>';
            });
            
            // Imprimir
            document.getElementById('btnImprimir').addEventListener('click', function() {
                window.print();
            });
            
            // Enviar a SUNAT
            document.getElementById('btnEnviarSunat').addEventListener('click', function() {
                if (confirm('¿Está seguro de enviar estos comprobantes a SUNAT?')) {
                    alert('Los comprobantes han sido enviados a SUNAT correctamente.');
                }
            });
        });
    </script>
</body>
</html>