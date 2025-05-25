<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="nueva_venta.php">
                    <i class="bi bi-cart-plus me-2"></i>
                    Nueva Venta
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="ventas.php">
                    <i class="bi bi-journals me-2"></i>
                    Historial de Ventas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="productos.php">
                    <i class="bi bi-box-seam me-2"></i>
                    Productos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="categorias.php">
                    <i class="bi bi-tags me-2"></i>
                    Categorías
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="proveedores.php">
                    <i class="bi bi-truck me-2"></i>
                    Proveedores
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="clientes.php">
                    <i class="bi bi-people me-2"></i>
                    Clientes
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Reportes SUNAT</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="reportes/sunat_ventas.php">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Registro de Ventas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="reportes/sunat_compras.php">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Registro de Compras
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="reportes/pdt.php">
                    <i class="bi bi-file-earmark-arrow-down me-2"></i>
                    Exportar PDT
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="reportes/facturas_electronicas.php">
                    <i class="bi bi-file-earmark-code me-2"></i>
                    Facturas Electrónicas
                </a>
            </li>
        </ul>
        
        <?php if ($_SESSION['usuario_rol'] === 'Administrador'): ?>
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Administración</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="usuarios.php">
                    <i class="bi bi-person-badge me-2"></i>
                    Usuarios
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="configuracion.php">
                    <i class="bi bi-gear me-2"></i>
                    Configuración
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="sunat_config.php">
                    <i class="bi bi-shield-check me-2"></i>
                    Configuración SUNAT
                </a>
            </li>
        </ul>
        <?php endif; ?>
    </div>
</nav>