<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Ventas e Inventario - SUNAT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h3 class="mb-0 fw-bold">
                            <i class="bi bi-shop me-2"></i>SistemaVentas
                        </h3>
                        <p class="mb-0">Sistema de Ventas e Inventario con SUNAT</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger" role="alert">
                                Credenciales incorrectas. Por favor, intente nuevamente.
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_GET['registro']) && $_GET['registro'] == '1'): ?>
                            <div class="alert alert-success" role="alert">
                                Usuario registrado correctamente. Ya puede iniciar sesión.
                            </div>
                        <?php endif; ?>
                        <form action="controladores/autenticacion.php" method="post">
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuario</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Ingrese su usuario" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Ingrese su contraseña" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center py-3">
                        <div class="small">
                            ¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a>
                        </div>
                        <div class="small mt-2">Sistema de Ventas e Inventario &copy; 2025</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>