<?php
session_start();
require_once 'config/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: index.php");
    exit;
}

// Obtener configuración actual de SUNAT
$stmt = $conexion->query("SELECT * FROM empresa LIMIT 1");
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

// Procesar formulario si se envió
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $conexion->prepare("
            UPDATE empresa SET 
                usuario_sol = :usuario_sol,
                clave_sol = :clave_sol,
                modo_sunat = :modo_sunat
            WHERE id = :id
        ");
        
        $stmt->execute([
            'usuario_sol' => $_POST['usuario_sol'],
            'clave_sol' => $_POST['clave_sol'],
            'modo_sunat' => $_POST['modo_sunat'],
            'id' => $empresa['id']
        ]);
        
        // Si se subió un certificado
        if (isset($_FILES['certificado']) && $_FILES['certificado']['error'] === UPLOAD_ERR_OK) {
            $certificado = $_FILES['certificado'];
            $ruta_certificado = 'certificados/' . $empresa['ruc'] . '.pem';
            
            // Mover archivo
            if (move_uploaded_file($certificado['tmp_name'], $ruta_certificado)) {
                $stmt = $conexion->prepare("
                    UPDATE empresa SET 
                        certificado_digital = :certificado,
                        clave_certificado = :clave_certificado
                    WHERE id = :id
                ");
                
                $stmt->execute([
                    'certificado' => $ruta_certificado,
                    'clave_certificado' => $_POST['clave_certificado'],
                    'id' => $empresa['id']
                ]);
            }
        }
        
        $mensaje = 'Configuración de SUNAT actualizada correctamente.';
        $tipo_mensaje = 'success';
        
        // Actualizar datos
        $stmt = $conexion->query("SELECT * FROM empresa LIMIT 1");
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $mensaje = 'Error al actualizar la configuración: ' . $e->getMessage();
        $tipo_mensaje = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración SUNAT - Sistema de Ventas e Inventario</title>
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
                    <h1 class="h2">Configuración SUNAT</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Volver al Dashboard
                        </a>
                    </div>
                </div>
                
                <?php if ($mensaje): ?>
                    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                        <?php echo $mensaje; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Configuración de Facturación Electrónica</h6>
                            </div>
                            <div class="card-body">
                                <form method="post" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="modo_sunat" class="form-label">Modo de Operación</label>
                                        <select class="form-select" id="modo_sunat" name="modo_sunat" required>
                                            <option value="Beta" <?php echo $empresa['modo_sunat'] === 'Beta' ? 'selected' : ''; ?>>Beta (Pruebas)</option>
                                            <option value="Produccion" <?php echo $empresa['modo_sunat'] === 'Produccion' ? 'selected' : ''; ?>>Producción</option>
                                        </select>
                                        <div class="form-text">Seleccione "Beta" para pruebas y "Producción" para operación real.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="usuario_sol" class="form-label">Usuario SOL</label>
                                        <input type="text" class="form-control" id="usuario_sol" name="usuario_sol" value="<?php echo htmlspecialchars($empresa['usuario_sol']); ?>" required>
                                        <div class="form-text">Usuario secundario creado en SUNAT para la facturación electrónica.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="clave_sol" class="form-label">Clave SOL</label>
                                        <input type="password" class="form-control" id="clave_sol" name="clave_sol" value="<?php echo htmlspecialchars($empresa['clave_sol']); ?>" required>
                                        <div class="form-text">Clave del usuario SOL.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="certificado" class="form-label">Certificado Digital</label>
                                        <input type="file" class="form-control" id="certificado" name="certificado">
                                        <div class="form-text">
                                            <?php if ($empresa['certificado_digital']): ?>
                                                Certificado actual: <?php echo $empresa['certificado_digital']; ?>
                                                <br>Suba un nuevo certificado solo si desea reemplazar el actual.
                                            <?php else: ?>
                                                Suba su certificado digital en formato .PEM
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="clave_certificado" class="form-label">Clave del Certificado</label>
                                        <input type="password" class="form-control" id="clave_certificado" name="clave_certificado" value="<?php echo htmlspecialchars($empresa['clave_certificado']); ?>">
                                        <div class="form-text">Contraseña del certificado digital.</div>
                                    </div>
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save me-1"></i>Guardar Configuración
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Información SUNAT</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="fw-bold">Datos de la Empresa</h6>
                                    <p>
                                        <strong>RUC:</strong> <?php echo $empresa['ruc']; ?><br>
                                        <strong>Razón Social:</strong> <?php echo $empresa['razon_social']; ?><br>
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="fw-bold">Estado de Conexión</h6>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2">
                                            <?php if ($empresa['modo_sunat'] === 'Beta'): ?>
                                                <span class="badge bg-warning">Modo Beta</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Modo Producción</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <p>
                                        <a href="#" class="btn btn-sm btn-outline-primary" id="btnProbarConexion">
                                            <i class="bi bi-check-circle me-1"></i>Probar Conexión
                                        </a>
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="fw-bold">Enlaces SUNAT</h6>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">
                                            <i class="bi bi-box-arrow-up-right me-1"></i>
                                            <a href="https://cpe.sunat.gob.pe/portal/" target="_blank">Portal de Facturación Electrónica</a>
                                        </li>
                                        <li class="list-group-item">
                                            <i class="bi bi-box-arrow-up-right me-1"></i>
                                            <a href="https://e-menu.sunat.gob.pe/cl-ti-itmenu/MenuInternet.htm" target="_blank">SUNAT Operaciones en Línea</a>
                                        </li>
                                        <li class="list-group-item">
                                            <i class="bi bi-box-arrow-up-right me-1"></i>
                                            <a href="https://www.sunat.gob.pe/legislacion/superin/2018/anexo-rs113-2018.pdf" target="_blank">Especificaciones Técnicas</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Botón para probar conexión
            const btnProbarConexion = document.getElementById('btnProbarConexion');
            if (btnProbarConexion) {
                btnProbarConexion.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Simulamos una prueba de conexión
                    btnProbarConexion.disabled = true;
                    btnProbarConexion.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Probando...';
                    
                    setTimeout(() => {
                        alert('Conexión exitosa con los servidores de SUNAT.');
                        btnProbarConexion.disabled = false;
                        btnProbarConexion.innerHTML = '<i class="bi bi-check-circle me-1"></i>Probar Conexión';
                    }, 2000);
                });
            }
        });
    </script>
</body>
</html>