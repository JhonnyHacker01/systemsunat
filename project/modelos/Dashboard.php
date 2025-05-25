<?php
/**
 * Clase para gestionar información del dashboard
 */
class Dashboard
{
    private $db;

    public function __construct($conexion)
    {
        $this->db = $conexion;
    }

    /**
     * Obtiene estadísticas principales para el dashboard
     */
    public function obtenerEstadisticas()
    {
        // Ventas del mes actual
        $inicioMes = date('Y-m-01');
        $finMes = date('Y-m-t');
        
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(total), 0) as ventasMes 
            FROM ventas 
            WHERE fecha_emision BETWEEN ? AND ? 
            AND estado = 'Completada'
        ");
        $stmt->execute([$inicioMes, $finMes]);
        $ventasMes = $stmt->fetch(PDO::FETCH_ASSOC)['ventasMes'];
        
        // Total de productos
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM productos WHERE estado = 1");
        $totalProductos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total de clientes
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM clientes WHERE estado = 1");
        $totalClientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Alertas de stock
        $stmt = $this->db->query("
            SELECT COUNT(*) as total 
            FROM productos 
            WHERE stock <= stock_minimo AND estado = 1
        ");
        $alertasStock = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return [
            'ventasMes' => $ventasMes,
            'totalProductos' => $totalProductos,
            'totalClientes' => $totalClientes,
            'alertasStock' => $alertasStock
        ];
    }
    
    /**
     * Obtiene las ventas más recientes
     */
    public function obtenerVentasRecientes($limite = 5)
    {
        $stmt = $this->db->prepare("
            SELECT 
                v.id,
                v.serie, 
                v.numero, 
                v.tipo_comprobante,
                v.fecha_emision as fecha,
                v.total,
                v.estado,
                c.razon_social as cliente
            FROM 
                ventas v
            JOIN 
                clientes c ON v.id_cliente = c.id
            ORDER BY 
                v.fecha_emision DESC
            LIMIT ?
        ");
        $stmt->bindParam(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene productos con stock bajo
     */
    public function obtenerProductosPocoStock($limite = 5)
    {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                codigo,
                nombre,
                stock,
                stock_minimo
            FROM 
                productos
            WHERE 
                stock <= stock_minimo
                AND estado = 1
            ORDER BY 
                (stock_minimo - stock) DESC
            LIMIT ?
        ");
        $stmt->bindParam(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene datos para el gráfico de ventas por mes
     */
    public function obtenerVentasPorMes()
    {
        $anioActual = date('Y');
        
        $stmt = $this->db->prepare("
            SELECT 
                MONTH(fecha_emision) as mes,
                SUM(total) as total
            FROM 
                ventas
            WHERE 
                YEAR(fecha_emision) = ?
                AND estado = 'Completada'
            GROUP BY 
                MONTH(fecha_emision)
            ORDER BY 
                mes
        ");
        $stmt->execute([$anioActual]);
        
        $ventas = [];
        for ($i = 1; $i <= 12; $i++) {
            $ventas[$i] = 0;
        }
        
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $ventas[$row['mes']] = (float) $row['total'];
        }
        
        return $ventas;
    }
}