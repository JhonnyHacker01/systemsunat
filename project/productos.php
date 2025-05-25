<?php
session_start();
require_once 'config/conexion.php';
require_once 'modelos/Producto.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$productoModel = new Producto($conexion);
$productos = $productoModel->listarActivos();

// Obtener categorías
$stmt = $conexion->query("SELECT * FROM categorias WHERE estado = 1 ORDER BY nombre");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener unidades de medida
$stmt = $conexion->query("SELECT * FROM unidades_medida WHERE estado = 1 ORDER BY nombre");
$unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - Sistema de Ventas e Inventario</title>
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
                    <h1 class="h2">Gestión de Productos</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProducto">
                            <i class="bi bi-plus-lg me-1"></i>Nuevo Producto
                        </button>
                    </div>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php
                        switch ($_GET['success']) {
                            case '1':
                                echo 'Producto agregado correctamente.';
                                break;
                            case '2':
                                echo 'Producto actualizado correctamente.';
                                break;
                            case '3':
                                echo 'Producto eliminado correctamente.';
                                break;
                            case '4':
                                echo 'Stock actualizado correctamente.';
                                break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php
                        switch ($_GET['error']) {
                            case '1':
                                echo 'Error al agregar el producto.';
                                break;
                            case '2':
                                echo 'Error al actualizar el producto.';
                                break;
                            case '3':
                                echo 'Error al eliminar el producto.';
                                break;
                            case '4':
                                echo 'Error al actualizar el stock.';
                                break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Categoría</th>
                                        <th>Stock</th>
                                        <th>Precio Compra</th>
                                        <th>Precio Venta</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($productos as $producto): ?>
                                        <tr>
                                            <td><?php echo $producto['codigo']; ?></td>
                                            <td><?php echo $producto['nombre']; ?></td>
                                            <td><?php echo $producto['categoria']; ?></td>
                                            <td class="text-center">
                                                <?php echo $producto['stock']; ?>
                                                <?php if ($producto['stock'] <= $producto['stock_minimo']): ?>
                                                    <span class="badge bg-danger">Stock Bajo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">S/. <?php echo number_format($producto['precio_compra'], 2); ?></td>
                                            <td class="text-end">S/. <?php echo number_format($producto['precio_venta'], 2); ?></td>
                                            <td class="text-center">
                                                <?php if ($producto['estado']): ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" onclick="editarProducto(<?php echo htmlspecialchars(json_encode($producto)); ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning" onclick="ajustarStock(<?php echo $producto['id']; ?>)">
                                                    <i class="bi bi-box-seam"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="eliminarProducto(<?php echo $producto['id']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Producto -->
    <div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="modalProductoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProductoLabel">Nuevo Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formProducto" action="controladores/productos.php" method="post">
                        <input type="hidden" name="accion" value="agregar">
                        <input type="hidden" name="id" id="producto_id">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="codigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" required>
                            </div>
                            <div class="col-md-6">
                                <label for="codigo_sunat" class="form-label">Código SUNAT</label>
                                <input type="text" class="form-control" id="codigo_sunat" name="codigo_sunat">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="categoria" class="form-label">Categoría</label>
                                <select class="form-select" id="categoria" name="categoria" required>
                                    <option value="">Seleccione una categoría</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?php echo $categoria['id']; ?>">
                                            <?php echo $categoria['nombre']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="unidad_medida" class="form-label">Unidad de Medida</label>
                                <select class="form-select" id="unidad_medida" name="unidad_medida" required>
                                    <option value="">Seleccione una unidad</option>
                                    <?php foreach ($unidades as $unidad): ?>
                                        <option value="<?php echo $unidad['id']; ?>">
                                            <?php echo $unidad['nombre'] . ' (' . $unidad['abreviatura'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="precio_compra" class="form-label">Precio de Compra</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/.</span>
                                    <input type="number" class="form-control" id="precio_compra" name="precio_compra" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="precio_venta" class="form-label">Precio de Venta</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/.</span>
                                    <input type="number" class="form-control" id="precio_venta" name="precio_venta" step="0.01" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="stock" class="form-label">Stock Inicial</label>
                                <input type="number" class="form-control" id="stock" name="stock" value="0" required>
                            </div>
                            <div class="col-md-6">
                                <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                                <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" value="10" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="afecto_igv" name="afecto_igv" checked>
                                <label class="form-check-label" for="afecto_igv">
                                    Afecto a IGV
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formProducto" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ajuste Stock -->
    <div class="modal fade" id="modalStock" tabindex="-1" aria-labelledby="modalStockLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalStockLabel">Ajustar Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formStock" action="controladores/productos.php" method="post">
                        <input type="hidden" name="accion" value="ajustar_stock">
                        <input type="hidden" name="id" id="stock_producto_id">
                        
                        <div class="mb-3">
                            <label for="tipo_movimiento" class="form-label">Tipo de Movimiento</label>
                            <select class="form-select" id="tipo_movimiento" name="tipo_movimiento" required>
                                <option value="Entrada">Entrada</option>
                                <option value="Salida">Salida</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cantidad" class="form-label">Cantidad</label>
                            <input type="number" class="form-control" id="cantidad" name="cantidad" required min="1">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formStock" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para editar producto
        function editarProducto(producto) {
            document.getElementById('formProducto').accion.value = 'editar';
            document.getElementById('producto_id').value = producto.id;
            document.getElementById('codigo').value = producto.codigo;
            document.getElementById('codigo_sunat').value = producto.codigo_sunat;
            document.getElementById('nombre').value = producto.nombre;
            document.getElementById('descripcion').value = producto.descripcion;
            document.getElementById('categoria').value = producto.id_categoria;
            document.getElementById('unidad_medida').value = producto.id_unidad_medida;
            document.getElementById('precio_compra').value = producto.precio_compra;
            document.getElementById('precio_venta').value = producto.precio_venta;
            document.getElementById('stock_minimo').value = producto.stock_minimo;
            document.getElementById('afecto_igv').checked = producto.afecto_igv == 1;
            
            // Ocultar campo de stock inicial en edición
            document.getElementById('stock').parentElement.style.display = 'none';
            
            document.getElementById('modalProductoLabel').textContent = 'Editar Producto';
            new bootstrap.Modal(document.getElementById('modalProducto')).show();
        }

        // Función para ajustar stock
        function ajustarStock(id) {
            document.getElementById('stock_producto_id').value = id;
            new bootstrap.Modal(document.getElementById('modalStock')).show();
        }

        // Función para eliminar producto
        function eliminarProducto(id) {
            if (confirm('¿Está seguro de eliminar este producto?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'controladores/productos.php';
                
                const accion = document.createElement('input');
                accion.type = 'hidden';
                accion.name = 'accion';
                accion.value = 'eliminar';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;
                
                form.appendChild(accion);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Limpiar formulario al abrir modal de nuevo producto
        document.getElementById('modalProducto').addEventListener('show.bs.modal', function (event) {
            if (!event.relatedTarget.classList.contains('btn-info')) {
                document.getElementById('formProducto').reset();
                document.getElementById('formProducto').accion.value = 'agregar';
                document.getElementById('producto_id').value = '';
                document.getElementById('stock').parentElement.style.display = 'block';
                document.getElementById('modalProductoLabel').textContent = 'Nuevo Producto';
            }
        });
    </script>
</body>
</html>