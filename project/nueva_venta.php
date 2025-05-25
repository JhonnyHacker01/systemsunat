<?php
session_start();
require_once 'config/conexion.php';
require_once 'modelos/Producto.php';
require_once 'modelos/Cliente.php';
require_once 'modelos/Venta.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$productoModel = new Producto($conexion);
$clienteModel = new Cliente($conexion);
$ventaModel = new Venta($conexion);

$productos = $productoModel->listarActivos();
$clientes = $clienteModel->listarActivos();
$series = $ventaModel->obtenerSeries();

// Obtener tipo de cambio dólar (en implementación real, se obtendría de una API)
$tipoCambioDolar = 3.70;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Venta - Sistema de Ventas e Inventario</title>
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
                    <h1 class="h2">Nueva Venta</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="ventas.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Volver a Ventas
                        </a>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Registrar Nueva Venta</h6>
                        <div>
                            <span class="badge bg-info">Tipo Cambio USD: S/. <?php echo number_format($tipoCambioDolar, 2); ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="formVenta" method="post" action="controladores/procesar_venta.php">
                            <!-- Datos del comprobante -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <label for="tipo_comprobante" class="form-label">Tipo Comprobante</label>
                                    <select class="form-select" id="tipo_comprobante" name="tipo_comprobante" required>
                                        <option value="">Seleccione</option>
                                        <option value="Factura">Factura Electrónica</option>
                                        <option value="Boleta">Boleta Electrónica</option>
                                        <option value="Nota de Venta">Nota de Venta</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="serie" class="form-label">Serie</label>
                                    <select class="form-select" id="serie" name="serie" required>
                                        <option value="">Seleccione</option>
                                        <?php foreach ($series as $serie): ?>
                                            <option value="<?php echo $serie['serie']; ?>" data-tipo="<?php echo $serie['tipo_documento']; ?>">
                                                <?php echo $serie['serie']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="numero" class="form-label">Número</label>
                                    <input type="text" class="form-control" id="numero" name="numero" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label for="fecha_emision" class="form-label">Fecha Emisión</label>
                                    <input type="datetime-local" class="form-control" id="fecha_emision" name="fecha_emision" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                                </div>
                                <div class="col-md-2">
                                    <label for="moneda" class="form-label">Moneda</label>
                                    <select class="form-select" id="moneda" name="moneda" required>
                                        <option value="PEN" selected>Soles (PEN)</option>
                                        <option value="USD">Dólares (USD)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Datos del cliente -->
                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <label for="cliente" class="form-label">Cliente</label>
                                    <div class="input-group">
                                        <select class="form-select" id="cliente" name="cliente_id" required>
                                            <option value="">Seleccione un cliente</option>
                                            <?php foreach ($clientes as $cliente): ?>
                                                <option value="<?php echo $cliente['id']; ?>" data-documento="<?php echo $cliente['tipo_documento']; ?>">
                                                    <?php echo $cliente['razon_social'] . ' - ' . $cliente['numero_documento']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente">
                                            <i class="bi bi-person-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="forma_pago" class="form-label">Forma de Pago</label>
                                    <select class="form-select" id="forma_pago" name="forma_pago" required>
                                        <option value="Contado" selected>Contado</option>
                                        <option value="Crédito">Crédito</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Agregar productos -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Agregar Productos</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="input-group mb-3">
                                                        <select class="form-select" id="producto_select">
                                                            <option value="">Buscar producto...</option>
                                                            <?php foreach ($productos as $producto): ?>
                                                                <option value="<?php echo $producto['id']; ?>" 
                                                                    data-nombre="<?php echo $producto['nombre']; ?>"
                                                                    data-precio="<?php echo $producto['precio_venta']; ?>"
                                                                    data-stock="<?php echo $producto['stock']; ?>"
                                                                    data-codigo="<?php echo $producto['codigo']; ?>"
                                                                    data-afecto-igv="<?php echo $producto['afecto_igv']; ?>">
                                                                    <?php echo $producto['nombre'] . ' - ' . $producto['codigo']; ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <button class="btn btn-primary" type="button" id="btnAgregarProducto">
                                                            <i class="bi bi-plus-lg"></i> Agregar
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text">Cant.</span>
                                                        <input type="number" class="form-control" id="cantidad_producto" value="1" min="1">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text">Precio</span>
                                                        <input type="number" class="form-control" id="precio_producto" step="0.01">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tabla de productos -->
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered table-hover" id="tablaProductos">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="10%">Código</th>
                                            <th width="40%">Producto</th>
                                            <th width="10%">Cantidad</th>
                                            <th width="15%">Precio Unit.</th>
                                            <th width="15%">Subtotal</th>
                                            <th width="10%">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="filaVacia">
                                            <td colspan="6" class="text-center">No hay productos agregados</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Totales -->
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="form-group mb-3">
                                        <label for="observaciones" class="form-label">Observaciones</label>
                                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="card border">
                                        <div class="card-body">
                                            <div class="row mb-2">
                                                <div class="col-6">Subtotal</div>
                                                <div class="col-6 text-end" id="subtotal">S/. 0.00</div>
                                                <input type="hidden" name="subtotal_valor" id="subtotal_valor" value="0">
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-6">IGV (18%)</div>
                                                <div class="col-6 text-end" id="igv">S/. 0.00</div>
                                                <input type="hidden" name="igv_valor" id="igv_valor" value="0">
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-6 fw-bold">TOTAL</div>
                                                <div class="col-6 text-end fw-bold" id="total">S/. 0.00</div>
                                                <input type="hidden" name="total_valor" id="total_valor" value="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botones de acción -->
                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-secondary me-2" onclick="window.location.href='ventas.php'">Cancelar</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i>Guardar Venta
                                </button>
                            </div>
                            
                            <!-- Productos en formato JSON -->
                            <input type="hidden" name="productos_json" id="productos_json" value="[]">
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Modal Nuevo Cliente -->
    <div class="modal fade" id="modalNuevoCliente" tabindex="-1" aria-labelledby="modalNuevoClienteLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoClienteLabel">Registrar Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoCliente">
                        <div class="mb-3">
                            <label for="tipo_documento" class="form-label">Tipo Documento</label>
                            <select class="form-select" id="cliente_tipo_documento" name="tipo_documento" required>
                                <option value="DNI">DNI</option>
                                <option value="RUC">RUC</option>
                                <option value="CE">Carnet Extranjería</option>
                                <option value="PASAPORTE">Pasaporte</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="numero_documento" class="form-label">Número de Documento</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="cliente_documento" name="numero_documento" required>
                                <button class="btn btn-outline-secondary" type="button" id="btnBuscarDocumento">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="razon_social" class="form-label">Razón Social / Nombres</label>
                            <input type="text" class="form-control" id="cliente_razon_social" name="razon_social" required>
                        </div>
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="cliente_direccion" name="direccion">
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="cliente_telefono" name="telefono">
                        </div>
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="cliente_correo" name="correo">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarCliente">Guardar Cliente</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/ventas.js"></script>
</body>
</html>