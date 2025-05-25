-- --------------------------------------------------------
-- Base de datos: sistema_ventas_sunat
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS sistema_ventas_sunat DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_ventas_sunat;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla usuarios
-- --------------------------------------------------------

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  rol ENUM('Administrador', 'Vendedor', 'Almacén') NOT NULL,
  correo VARCHAR(100) NOT NULL,
  estado BOOLEAN DEFAULT TRUE,
  ultimo_login DATETIME DEFAULT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar usuario administrador por defecto (password: admin123)
INSERT INTO usuarios (nombre, usuario, password, rol, correo) 
VALUES ('Administrador', 'admin', '$2y$10$qUJ.z9eFkYC7P/s7ih7Nnu1LbL9fgZLITxKP/o7wfpVu75LRLRUSO', 'Administrador', 'admin@sistema.com');

-- --------------------------------------------------------
-- Estructura de tabla para la tabla categorias
-- --------------------------------------------------------

CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT,
  estado BOOLEAN DEFAULT TRUE,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar algunas categorías por defecto
INSERT INTO categorias (nombre, descripcion) VALUES
('Electrónica', 'Productos electrónicos y accesorios'),
('Hogar', 'Artículos para el hogar'),
('Oficina', 'Materiales y equipos de oficina'),
('Computación', 'Equipos de computación y accesorios');

-- --------------------------------------------------------
-- Estructura de tabla para la tabla unidades_medida
-- --------------------------------------------------------

CREATE TABLE unidades_medida (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(5) NOT NULL UNIQUE,
  nombre VARCHAR(50) NOT NULL,
  abreviatura VARCHAR(10) NOT NULL,
  estado BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar unidades de medida SUNAT
INSERT INTO unidades_medida (codigo, nombre, abreviatura) VALUES
('NIU', 'UNIDAD', 'UND'),
('KGM', 'KILOGRAMO', 'KG'),
('LTR', 'LITRO', 'L'),
('MTR', 'METRO', 'M'),
('BX', 'CAJA', 'CAJA'),
('PK', 'PAQUETE', 'PAQ');

-- --------------------------------------------------------
-- Estructura de tabla para la tabla proveedores
-- --------------------------------------------------------

CREATE TABLE proveedores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo_documento ENUM('RUC', 'DNI') NOT NULL,
  numero_documento VARCHAR(20) NOT NULL UNIQUE,
  razon_social VARCHAR(200) NOT NULL,
  direccion VARCHAR(255),
  telefono VARCHAR(15),
  correo VARCHAR(100),
  contacto VARCHAR(100),
  estado BOOLEAN DEFAULT TRUE,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla productos
-- --------------------------------------------------------

CREATE TABLE productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(20) NOT NULL UNIQUE,
  nombre VARCHAR(200) NOT NULL,
  descripcion TEXT,
  id_categoria INT NOT NULL,
  id_unidad_medida INT NOT NULL,
  precio_compra DECIMAL(10,2) NOT NULL,
  precio_venta DECIMAL(10,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  stock_minimo INT DEFAULT 10,
  afecto_igv BOOLEAN DEFAULT TRUE,
  codigo_sunat VARCHAR(8),
  estado BOOLEAN DEFAULT TRUE,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_categoria) REFERENCES categorias(id),
  FOREIGN KEY (id_unidad_medida) REFERENCES unidades_medida(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla clientes
-- --------------------------------------------------------

CREATE TABLE clientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo_documento ENUM('DNI', 'RUC', 'CE', 'PASAPORTE') NOT NULL,
  numero_documento VARCHAR(20) NOT NULL UNIQUE,
  razon_social VARCHAR(200) NOT NULL,
  direccion VARCHAR(255),
  telefono VARCHAR(15),
  correo VARCHAR(100),
  estado BOOLEAN DEFAULT TRUE,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar cliente genérico para ventas rápidas
INSERT INTO clientes (tipo_documento, numero_documento, razon_social, direccion) 
VALUES ('DNI', '00000000', 'CLIENTE GENÉRICO', 'SIN DIRECCIÓN');

-- --------------------------------------------------------
-- Estructura de tabla para la tabla ventas
-- --------------------------------------------------------

CREATE TABLE ventas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  serie VARCHAR(4) NOT NULL,
  numero VARCHAR(8) NOT NULL,
  id_cliente INT NOT NULL,
  fecha_emision DATETIME NOT NULL,
  fecha_vencimiento DATETIME,
  tipo_comprobante ENUM('Factura', 'Boleta', 'Nota de Venta') NOT NULL,
  tipo_moneda ENUM('PEN', 'USD') DEFAULT 'PEN',
  subtotal DECIMAL(10,2) NOT NULL,
  igv DECIMAL(10,2) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  estado ENUM('Completada', 'Anulada', 'Pendiente') DEFAULT 'Completada',
  id_usuario INT NOT NULL,
  enviado_sunat BOOLEAN DEFAULT FALSE,
  respuesta_sunat TEXT,
  observaciones TEXT,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_cliente) REFERENCES clientes(id),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
  UNIQUE KEY (serie, numero, tipo_comprobante)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla detalle_ventas
-- --------------------------------------------------------

CREATE TABLE detalle_ventas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_venta INT NOT NULL,
  id_producto INT NOT NULL,
  cantidad INT NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  igv DECIMAL(10,2) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (id_venta) REFERENCES ventas(id) ON DELETE CASCADE,
  FOREIGN KEY (id_producto) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla compras
-- --------------------------------------------------------

CREATE TABLE compras (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_proveedor INT NOT NULL,
  tipo_comprobante ENUM('Factura', 'Boleta', 'Guía') NOT NULL,
  serie_comprobante VARCHAR(10) NOT NULL,
  num_comprobante VARCHAR(10) NOT NULL,
  fecha DATETIME NOT NULL,
  impuesto DECIMAL(4,2) DEFAULT 18.00,
  subtotal DECIMAL(10,2) NOT NULL,
  igv DECIMAL(10,2) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  estado ENUM('Recibida', 'Anulada', 'Pendiente') DEFAULT 'Recibida',
  id_usuario INT NOT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_proveedor) REFERENCES proveedores(id),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla detalle_compras
-- --------------------------------------------------------

CREATE TABLE detalle_compras (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_compra INT NOT NULL,
  id_producto INT NOT NULL,
  cantidad INT NOT NULL,
  precio_compra DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (id_compra) REFERENCES compras(id) ON DELETE CASCADE,
  FOREIGN KEY (id_producto) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla empresa
-- --------------------------------------------------------

CREATE TABLE empresa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ruc VARCHAR(11) NOT NULL,
  razon_social VARCHAR(200) NOT NULL,
  nombre_comercial VARCHAR(200),
  direccion VARCHAR(255) NOT NULL,
  distrito VARCHAR(100),
  provincia VARCHAR(100),
  departamento VARCHAR(100),
  telefono VARCHAR(15),
  correo VARCHAR(100),
  logo VARCHAR(255),
  certificado_digital VARCHAR(255),
  usuario_sol VARCHAR(20),
  clave_sol VARCHAR(255),
  clave_certificado VARCHAR(255),
  modo_sunat ENUM('Beta', 'Produccion') DEFAULT 'Beta',
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar datos de ejemplo para empresa
INSERT INTO empresa (ruc, razon_social, nombre_comercial, direccion, distrito, provincia, departamento, telefono, correo) 
VALUES ('20505687452', 'EMPRESA EJEMPLO S.A.C.', 'MI EMPRESA', 'Av. Principal 123', 'Lima', 'Lima', 'Lima', '(01) 555-1234', 'contacto@miempresa.com');

-- --------------------------------------------------------
-- Estructura de tabla para la tabla series_documentos
-- --------------------------------------------------------

CREATE TABLE series_documentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo_documento ENUM('Factura', 'Boleta', 'Nota de Crédito', 'Nota de Débito', 'Guía') NOT NULL,
  serie VARCHAR(4) NOT NULL,
  correlativo INT NOT NULL DEFAULT 1,
  UNIQUE KEY (tipo_documento, serie)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar series por defecto
INSERT INTO series_documentos (tipo_documento, serie) VALUES
('Factura', 'F001'),
('Boleta', 'B001'),
('Nota de Crédito', 'FC01'),
('Nota de Crédito', 'BC01'),
('Nota de Débito', 'FD01'),
('Nota de Débito', 'BD01'),
('Guía', 'T001');

-- --------------------------------------------------------
-- Estructura de tabla para la tabla notas_credito
-- --------------------------------------------------------

CREATE TABLE notas_credito (
  id INT AUTO_INCREMENT PRIMARY KEY,
  serie VARCHAR(4) NOT NULL,
  numero VARCHAR(8) NOT NULL,
  id_venta INT NOT NULL,
  fecha_emision DATETIME NOT NULL,
  motivo ENUM('Anulación de la operación', 'Anulación por error en el RUC', 'Corrección por error en la descripción', 'Descuento global', 'Descuento por ítem', 'Devolución total', 'Devolución por ítem', 'Bonificación', 'Disminución en el valor', 'Otros') NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  igv DECIMAL(10,2) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  estado ENUM('Emitida', 'Anulada') DEFAULT 'Emitida',
  id_usuario INT NOT NULL,
  enviado_sunat BOOLEAN DEFAULT FALSE,
  respuesta_sunat TEXT,
  observaciones TEXT,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_venta) REFERENCES ventas(id),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
  UNIQUE KEY (serie, numero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla detalle_notas_credito
-- --------------------------------------------------------

CREATE TABLE detalle_notas_credito (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_nota_credito INT NOT NULL,
  id_producto INT NOT NULL,
  cantidad INT NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  igv DECIMAL(10,2) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (id_nota_credito) REFERENCES notas_credito(id) ON DELETE CASCADE,
  FOREIGN KEY (id_producto) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla inventario_movimientos
-- --------------------------------------------------------

CREATE TABLE inventario_movimientos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_producto INT NOT NULL,
  tipo_movimiento ENUM('Entrada', 'Salida', 'Ajuste') NOT NULL,
  cantidad INT NOT NULL,
  fecha DATETIME NOT NULL,
  referencia VARCHAR(100),
  id_referencia INT,
  tipo_referencia ENUM('Compra', 'Venta', 'Ajuste', 'Nota Crédito') NOT NULL,
  id_usuario INT NOT NULL,
  observaciones TEXT,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_producto) REFERENCES productos(id),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Vistas y Procedimientos almacenados
-- --------------------------------------------------------

-- Vista para consulta rápida de productos
CREATE VIEW vista_productos AS
SELECT 
    p.id, 
    p.codigo, 
    p.nombre, 
    p.descripcion, 
    p.precio_compra, 
    p.precio_venta, 
    p.stock, 
    p.stock_minimo,
    c.nombre AS categoria,
    u.nombre AS unidad_medida,
    u.abreviatura AS unidad_abreviatura
FROM 
    productos p
JOIN 
    categorias c ON p.id_categoria = c.id
JOIN 
    unidades_medida u ON p.id_unidad_medida = u.id
WHERE 
    p.estado = 1;

-- Vista para resumen de ventas
CREATE VIEW vista_ventas AS
SELECT 
    v.id,
    v.serie,
    v.numero,
    CONCAT(v.serie, '-', v.numero) AS comprobante,
    v.fecha_emision,
    v.tipo_comprobante,
    v.subtotal,
    v.igv,
    v.total,
    v.estado,
    c.razon_social AS cliente,
    c.numero_documento AS cliente_documento,
    u.nombre AS usuario
FROM 
    ventas v
JOIN 
    clientes c ON v.id_cliente = c.id
JOIN 
    usuarios u ON v.id_usuario = u.id;

-- Procedimiento para registrar una venta
DELIMITER $$
CREATE PROCEDURE sp_registrar_venta(
    IN p_serie VARCHAR(4),
    IN p_numero VARCHAR(8),
    IN p_id_cliente INT,
    IN p_fecha_emision DATETIME,
    IN p_fecha_vencimiento DATETIME,
    IN p_tipo_comprobante ENUM('Factura', 'Boleta', 'Nota de Venta'),
    IN p_tipo_moneda ENUM('PEN', 'USD'),
    IN p_subtotal DECIMAL(10,2),
    IN p_igv DECIMAL(10,2),
    IN p_total DECIMAL(10,2),
    IN p_id_usuario INT,
    IN p_observaciones TEXT,
    OUT p_id_venta INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_id_venta = 0;
    END;
    
    START TRANSACTION;
    
    INSERT INTO ventas (
        serie, 
        numero, 
        id_cliente, 
        fecha_emision, 
        fecha_vencimiento, 
        tipo_comprobante, 
        tipo_moneda, 
        subtotal, 
        igv, 
        total, 
        id_usuario, 
        observaciones
    ) VALUES (
        p_serie,
        p_numero,
        p_id_cliente,
        p_fecha_emision,
        p_fecha_vencimiento,
        p_tipo_comprobante,
        p_tipo_moneda,
        p_subtotal,
        p_igv,
        p_total,
        p_id_usuario,
        p_observaciones
    );
    
    SET p_id_venta = LAST_INSERT_ID();
    
    -- Actualizar el correlativo de la serie
    UPDATE series_documentos 
    SET correlativo = correlativo + 1 
    WHERE tipo_documento = p_tipo_comprobante AND serie = p_serie;
    
    COMMIT;
END$$
DELIMITER ;

-- Procedimiento para registrar detalle de venta y actualizar stock
DELIMITER $$
CREATE PROCEDURE sp_registrar_detalle_venta(
    IN p_id_venta INT,
    IN p_id_producto INT,
    IN p_cantidad INT,
    IN p_precio_unitario DECIMAL(10,2),
    IN p_subtotal DECIMAL(10,2),
    IN p_igv DECIMAL(10,2),
    IN p_total DECIMAL(10,2),
    IN p_id_usuario INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;
    
    START TRANSACTION;
    
    -- Insertar detalle de venta
    INSERT INTO detalle_ventas (
        id_venta,
        id_producto,
        cantidad,
        precio_unitario,
        subtotal,
        igv,
        total
    ) VALUES (
        p_id_venta,
        p_id_producto,
        p_cantidad,
        p_precio_unitario,
        p_subtotal,
        p_igv,
        p_total
    );
    
    -- Actualizar stock del producto
    UPDATE productos 
    SET stock = stock - p_cantidad 
    WHERE id = p_id_producto;
    
    -- Registrar movimiento de inventario
    INSERT INTO inventario_movimientos (
        id_producto,
        tipo_movimiento,
        cantidad,
        fecha,
        referencia,
        id_referencia,
        tipo_referencia,
        id_usuario,
        observaciones
    ) VALUES (
        p_id_producto,
        'Salida',
        p_cantidad,
        NOW(),
        'Venta',
        p_id_venta,
        'Venta',
        p_id_usuario,
        'Venta de producto'
    );
    
    COMMIT;
END$$
DELIMITER ;

-- Procedimiento para obtener siguiente comprobante
DELIMITER $$
CREATE PROCEDURE sp_obtener_siguiente_comprobante(
    IN p_tipo_documento ENUM('Factura', 'Boleta', 'Nota de Crédito', 'Nota de Débito', 'Guía'),
    IN p_serie VARCHAR(4),
    OUT p_siguiente_correlativo VARCHAR(8)
)
BEGIN
    DECLARE v_correlativo INT;
    
    SELECT correlativo INTO v_correlativo
    FROM series_documentos
    WHERE tipo_documento = p_tipo_documento AND serie = p_serie;
    
    SET p_siguiente_correlativo = LPAD(v_correlativo, 8, '0');
END$$
DELIMITER ;