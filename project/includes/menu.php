<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <i class="bi bi-shop me-2"></i>SistemaVentas
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="ventas.php">
                        <i class="bi bi-cart me-1"></i>Ventas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="productos.php">
                        <i class="bi bi-box-seam me-1"></i>Productos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="clientes.php">
                        <i class="bi bi-people me-1"></i>Clientes
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownReportes" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-file-earmark-text me-1"></i>Reportes
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownReportes">
                        <li><a class="dropdown-item" href="reportes/ventas.php">Reporte de Ventas</a></li>
                        <li><a class="dropdown-item" href="reportes/inventario.php">Reporte de Inventario</a></li>
                        <li><a class="dropdown-item" href="reportes/sunat.php">Reportes SUNAT</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownConfig" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-gear me-1"></i>Configuración
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownConfig">
                        <li><a class="dropdown-item" href="configuracion.php">Empresa</a></li>
                        <li><a class="dropdown-item" href="usuarios.php">Usuarios</a></li>
                        <li><a class="dropdown-item" href="sunat_config.php">Configuración SUNAT</a></li>
                    </ul>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUsuario" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i><?php echo $_SESSION['usuario_nombre']; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUsuario">
                        <li><a class="dropdown-item" href="perfil.php">Mi Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="controladores/cerrar_sesion.php">Cerrar Sesión</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>