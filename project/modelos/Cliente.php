<?php
/**
 * Clase para gestionar clientes
 */
class Cliente
{
    private $db;

    public function __construct($conexion)
    {
        $this->db = $conexion;
    }

    /**
     * Obtiene un cliente por su ID
     */
    public function obtenerPorId($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene un cliente por su número de documento
     */
    public function obtenerPorDocumento($numero_documento)
    {
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE numero_documento = ?");
        $stmt->execute([$numero_documento]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lista todos los clientes activos
     */
    public function listarActivos()
    {
        $stmt = $this->db->query("
            SELECT * FROM clientes 
            WHERE estado = 1 
            ORDER BY razon_social
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Agrega un nuevo cliente
     */
    public function agregar($datos)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO clientes (
                    tipo_documento, 
                    numero_documento, 
                    razon_social, 
                    direccion, 
                    telefono, 
                    correo
                ) VALUES (
                    :tipo_documento, 
                    :numero_documento, 
                    :razon_social, 
                    :direccion, 
                    :telefono, 
                    :correo
                )
            ");
            
            $stmt->bindParam(':tipo_documento', $datos['tipo_documento']);
            $stmt->bindParam(':numero_documento', $datos['numero_documento']);
            $stmt->bindParam(':razon_social', $datos['razon_social']);
            $stmt->bindParam(':direccion', $datos['direccion']);
            $stmt->bindParam(':telefono', $datos['telefono']);
            $stmt->bindParam(':correo', $datos['correo']);
            
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            // Log del error
            error_log("Error al agregar cliente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza un cliente existente
     */
    public function actualizar($id, $datos)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE clientes SET 
                    tipo_documento = :tipo_documento, 
                    numero_documento = :numero_documento, 
                    razon_social = :razon_social, 
                    direccion = :direccion, 
                    telefono = :telefono, 
                    correo = :correo
                WHERE id = :id
            ");
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':tipo_documento', $datos['tipo_documento']);
            $stmt->bindParam(':numero_documento', $datos['numero_documento']);
            $stmt->bindParam(':razon_social', $datos['razon_social']);
            $stmt->bindParam(':direccion', $datos['direccion']);
            $stmt->bindParam(':telefono', $datos['telefono']);
            $stmt->bindParam(':correo', $datos['correo']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            // Log del error
            error_log("Error al actualizar cliente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Elimina un cliente (desactiva)
     */
    public function eliminar($id)
    {
        try {
            $stmt = $this->db->prepare("UPDATE clientes SET estado = 0 WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            // Log del error
            error_log("Error al eliminar cliente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Busca clientes por nombre o documento
     */
    public function buscar($termino)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM clientes
            WHERE (razon_social LIKE :termino OR numero_documento LIKE :termino)
            AND estado = 1
            ORDER BY razon_social
            LIMIT 20
        ");
        
        $termino = '%' . $termino . '%';
        $stmt->bindParam(':termino', $termino);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}