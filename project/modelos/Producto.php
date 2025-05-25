<?php
/**
 * Clase para gestionar productos
 */
class Producto
{
    private $db;

    public function __construct($conexion)
    {
        $this->db = $conexion;
    }

    /**
     * Obtiene un producto por su ID
     */
    public function obtenerPorId($id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                p.*,
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
                p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lista todos los productos activos
     */
    public function listarActivos()
    {
        $stmt = $this->db->query("
            SELECT 
                p.*,
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
                p.estado = 1
            ORDER BY 
                p.nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Agrega un nuevo producto
     */
    public function agregar($datos)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO productos (
                    codigo, 
                    nombre, 
                    descripcion, 
                    id_categoria, 
                    id_unidad_medida, 
                    precio_compra, 
                    precio_venta, 
                    stock, 
                    stock_minimo, 
                    afecto_igv, 
                    codigo_sunat
                ) VALUES (
                    :codigo, 
                    :nombre, 
                    :descripcion, 
                    :id_categoria, 
                    :id_unidad_medida, 
                    :precio_compra, 
                    :precio_venta, 
                    :stock, 
                    :stock_minimo, 
                    :afecto_igv, 
                    :codigo_sunat
                )
            ");
            
            $stmt->bindParam(':codigo', $datos['codigo']);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':descripcion', $datos['descripcion']);
            $stmt->bindParam(':id_categoria', $datos['id_categoria'], PDO::PARAM_INT);
            $stmt->bindParam(':id_unidad_medida', $datos['id_unidad_medida'], PDO::PARAM_INT);
            $stmt->bindParam(':precio_compra', $datos['precio_compra']);
            $stmt->bindParam(':precio_venta', $datos['precio_venta']);
            $stmt->bindParam(':stock', $datos['stock'], PDO::PARAM_INT);
            $stmt->bindParam(':stock_minimo', $datos['stock_minimo'], PDO::PARAM_INT);
            $stmt->bindParam(':afecto_igv', $datos['afecto_igv'], PDO::PARAM_BOOL);
            $stmt->bindParam(':codigo_sunat', $datos['codigo_sunat']);
            
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            // Log del error
            error_log("Error al agregar producto: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza un producto existente
     */
    public function actualizar($id, $datos)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE productos SET 
                    codigo = :codigo, 
                    nombre = :nombre, 
                    descripcion = :descripcion, 
                    id_categoria = :id_categoria, 
                    id_unidad_medida = :id_unidad_medida, 
                    precio_compra = :precio_compra, 
                    precio_venta = :precio_venta, 
                    stock_minimo = :stock_minimo, 
                    afecto_igv = :afecto_igv, 
                    codigo_sunat = :codigo_sunat
                WHERE id = :id
            ");
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':codigo', $datos['codigo']);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':descripcion', $datos['descripcion']);
            $stmt->bindParam(':id_categoria', $datos['id_categoria'], PDO::PARAM_INT);
            $stmt->bindParam(':id_unidad_medida', $datos['id_unidad_medida'], PDO::PARAM_INT);
            $stmt->bindParam(':precio_compra', $datos['precio_compra']);
            $stmt->bindParam(':precio_venta', $datos['precio_venta']);
            $stmt->bindParam(':stock_minimo', $datos['stock_minimo'], PDO::PARAM_INT);
            $stmt->bindParam(':afecto_igv', $datos['afecto_igv'], PDO::PARAM_BOOL);
            $stmt->bindParam(':codigo_sunat', $datos['codigo_sunat']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            // Log del error
            error_log("Error al actualizar producto: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza el stock de un producto
     */
    public function actualizarStock($id, $cantidad, $tipo = 'Entrada')
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE productos SET 
                    stock = CASE 
                        WHEN :tipo = 'Entrada' THEN stock + :cantidad 
                        WHEN :tipo = 'Salida' THEN stock - :cantidad 
                        ELSE stock + :cantidad 
                    END
                WHERE id = :id
            ");
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
            $stmt->bindParam(':tipo', $tipo);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            // Log del error
            error_log("Error al actualizar stock: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Elimina un producto (desactiva)
     */
    public function eliminar($id)
    {
        try {
            $stmt = $this->db->prepare("UPDATE productos SET estado = 0 WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            // Log del error
            error_log("Error al eliminar producto: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Busca productos por nombre o código
     */
    public function buscar($termino)
    {
        $stmt = $this->db->prepare("
            SELECT 
                p.*,
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
                (p.nombre LIKE :termino OR p.codigo LIKE :termino)
                AND p.estado = 1
            ORDER BY 
                p.nombre
            LIMIT 20
        ");
        
        $termino = '%' . $termino . '%';
        $stmt->bindParam(':termino', $termino);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lista productos con stock bajo
     */
    public function listarStockBajo()
    {
        $stmt = $this->db->query("
            SELECT 
                p.*,
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
                p.stock <= p.stock_minimo
                AND p.estado = 1
            ORDER BY 
                (p.stock_minimo - p.stock) DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}