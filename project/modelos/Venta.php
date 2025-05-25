<?php
/**
 * Clase para gestionar ventas
 */
class Venta
{
    private $db;

    public function __construct($conexion)
    {
        $this->db = $conexion;
    }

    /**
     * Obtiene una venta por su ID
     */
    public function obtenerPorId($id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                v.*,
                c.razon_social AS cliente,
                c.numero_documento AS cliente_documento,
                c.tipo_documento AS cliente_tipo_documento,
                c.direccion AS cliente_direccion,
                u.nombre AS usuario
            FROM 
                ventas v
            JOIN 
                clientes c ON v.id_cliente = c.id
            JOIN 
                usuarios u ON v.id_usuario = u.id
            WHERE 
                v.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene el detalle de una venta
     */
    public function obtenerDetalle($id_venta)
    {
        $stmt = $this->db->prepare("
            SELECT 
                d.*,
                p.nombre AS producto,
                p.codigo AS codigo_producto,
                p.afecto_igv,
                u.abreviatura AS unidad
            FROM 
                detalle_ventas d
            JOIN 
                productos p ON d.id_producto = p.id
            JOIN 
                unidades_medida u ON p.id_unidad_medida = u.id
            WHERE 
                d.id_venta = ?
        ");
        $stmt->execute([$id_venta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lista las ventas con paginación
     */
    public function listar($pagina = 1, $porPagina = 20, $filtros = [])
    {
        $offset = ($pagina - 1) * $porPagina;
        
        $where = "WHERE 1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filtros['cliente'])) {
            $where .= " AND (c.razon_social LIKE ? OR c.numero_documento LIKE ?)";
            $termino = '%' . $filtros['cliente'] . '%';
            $params[] = $termino;
            $params[] = $termino;
        }
        
        if (!empty($filtros['fecha_desde'])) {
            $where .= " AND DATE(v.fecha_emision) >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        
        if (!empty($filtros['fecha_hasta'])) {
            $where .= " AND DATE(v.fecha_emision) <= ?";
            $params[] = $filtros['fecha_hasta'];
        }
        
        if (!empty($filtros['tipo_comprobante'])) {
            $where .= " AND v.tipo_comprobante = ?";
            $params[] = $filtros['tipo_comprobante'];
        }
        
        if (isset($filtros['estado']) && $filtros['estado'] !== '') {
            $where .= " AND v.estado = ?";
            $params[] = $filtros['estado'];
        }
        
        // Consulta principal
        $sql = "
            SELECT 
                v.id,
                v.serie,
                v.numero,
                v.fecha_emision,
                v.tipo_comprobante,
                v.total,
                v.estado,
                v.enviado_sunat,
                c.razon_social AS cliente,
                c.numero_documento AS cliente_documento
            FROM 
                ventas v
            JOIN 
                clientes c ON v.id_cliente = c.id
            $where
            ORDER BY 
                v.fecha_emision DESC
            LIMIT ?, ?
        ";
        
        $params[] = $offset;
        $params[] = $porPagina;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Consulta para total de registros
        $sqlCount = "
            SELECT COUNT(*) as total
            FROM ventas v
            JOIN clientes c ON v.id_cliente = c.id
            $where
        ";
        
        array_pop($params); // Quitar limit
        array_pop($params); // Quitar offset
        
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute($params);
        $totalRegistros = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        
        return [
            'ventas' => $ventas,
            'total' => $totalRegistros,
            'paginas' => ceil($totalRegistros / $porPagina),
            'pagina_actual' => $pagina
        ];
    }
    
    /**
     * Registra una nueva venta
     */
    public function registrar($venta, $detalles)
    {
        try {
            $this->db->beginTransaction();
            
            // Registrar la venta
            $stmt = $this->db->prepare("
                CALL sp_registrar_venta(
                    :serie, 
                    :numero, 
                    :id_cliente, 
                    :fecha_emision, 
                    :fecha_vencimiento, 
                    :tipo_comprobante, 
                    :tipo_moneda, 
                    :subtotal, 
                    :igv, 
                    :total, 
                    :id_usuario, 
                    :observaciones,
                    @id_venta
                )
            ");
            
            $stmt->bindParam(':serie', $venta['serie']);
            $stmt->bindParam(':numero', $venta['numero']);
            $stmt->bindParam(':id_cliente', $venta['id_cliente'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha_emision', $venta['fecha_emision']);
            $stmt->bindParam(':fecha_vencimiento', $venta['fecha_vencimiento']);
            $stmt->bindParam(':tipo_comprobante', $venta['tipo_comprobante']);
            $stmt->bindParam(':tipo_moneda', $venta['tipo_moneda']);
            $stmt->bindParam(':subtotal', $venta['subtotal']);
            $stmt->bindParam(':igv', $venta['igv']);
            $stmt->bindParam(':total', $venta['total']);
            $stmt->bindParam(':id_usuario', $venta['id_usuario'], PDO::PARAM_INT);
            $stmt->bindParam(':observaciones', $venta['observaciones']);
            
            $stmt->execute();
            
            $this->db->query("SELECT @id_venta as id");
            $id_venta = $this->db->query("SELECT @id_venta as id")->fetch(PDO::FETCH_ASSOC)['id'];
            
            if (!$id_venta) {
                throw new Exception("Error al obtener el ID de la venta");
            }
            
            // Registrar los detalles
            foreach ($detalles as $detalle) {
                $stmt = $this->db->prepare("
                    CALL sp_registrar_detalle_venta(
                        :id_venta,
                        :id_producto,
                        :cantidad,
                        :precio_unitario,
                        :subtotal,
                        :igv,
                        :total,
                        :id_usuario
                    )
                ");
                
                $stmt->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
                $stmt->bindParam(':id_producto', $detalle['id_producto'], PDO::PARAM_INT);
                $stmt->bindParam(':cantidad', $detalle['cantidad'], PDO::PARAM_INT);
                $stmt->bindParam(':precio_unitario', $detalle['precio_unitario']);
                $stmt->bindParam(':subtotal', $detalle['subtotal']);
                $stmt->bindParam(':igv', $detalle['igv']);
                $stmt->bindParam(':total', $detalle['total']);
                $stmt->bindParam(':id_usuario', $venta['id_usuario'], PDO::PARAM_INT);
                
                $stmt->execute();
            }
            
            $this->db->commit();
            return $id_venta;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al registrar venta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Anula una venta
     */
    public function anular($id, $id_usuario, $motivo)
    {
        try {
            $this->db->beginTransaction();
            
            // Cambiar estado de la venta
            $stmt = $this->db->prepare("
                UPDATE ventas SET 
                    estado = 'Anulada',
                    observaciones = CONCAT(IFNULL(observaciones, ''), ' | ANULADO: ', ?)
                WHERE id = ?
            ");
            $stmt->execute([$motivo, $id]);
            
            // Devolver stock de productos
            $detalles = $this->obtenerDetalle($id);
            foreach ($detalles as $detalle) {
                $stmt = $this->db->prepare("
                    UPDATE productos SET 
                        stock = stock + ?
                    WHERE id = ?
                ");
                $stmt->execute([$detalle['cantidad'], $detalle['id_producto']]);
                
                // Registrar movimiento de inventario
                $stmt = $this->db->prepare("
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
                        ?,
                        'Entrada',
                        ?,
                        NOW(),
                        'Anulación de Venta',
                        ?,
                        'Venta',
                        ?,
                        ?
                    )
                ");
                $stmt->execute([
                    $detalle['id_producto'],
                    $detalle['cantidad'],
                    $id,
                    $id_usuario,
                    'Devolución por anulación de venta'
                ]);
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al anular venta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene las series de documentos para ventas
     */
    public function obtenerSeries()
    {
        $stmt = $this->db->query("
            SELECT * FROM series_documentos 
            WHERE tipo_documento IN ('Factura', 'Boleta', 'Nota de Venta')
            ORDER BY tipo_documento, serie
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene el siguiente número para una serie
     */
    public function obtenerSiguienteNumero($tipo_documento, $serie)
    {
        $stmt = $this->db->prepare("
            CALL sp_obtener_siguiente_comprobante(?, ?, @siguiente)
        ");
        $stmt->execute([$tipo_documento, $serie]);
        
        $result = $this->db->query("SELECT @siguiente as numero");
        return $result->fetch(PDO::FETCH_ASSOC)['numero'];
    }
    
    /**
     * Actualiza el estado de envío a SUNAT
     */
    public function actualizarEstadoSunat($id, $estado, $respuesta)
    {
        $stmt = $this->db->prepare("
            UPDATE ventas SET 
                enviado_sunat = ?,
                respuesta_sunat = ?
            WHERE id = ?
        ");
        return $stmt->execute([$estado, $respuesta, $id]);
    }
}