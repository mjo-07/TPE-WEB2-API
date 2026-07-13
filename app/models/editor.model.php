<?php
require_once 'Model.php';

class ModelEditor extends Model
{

    public function get($id)
    {
        $pdo = $this->crearConexion();
        $query = $pdo->prepare("SELECT * FROM editor WHERE id_editor = ?");
        $query->execute([$id]);
        $editor = $query->fetch(PDO::FETCH_OBJ);
        return $editor;
    }

    public function getAll($filtro, $valor, $orderBy, $dir, $page, $limit){
        $pdo = $this->crearConexion();

        $sql = "SELECT * FROM editor";
        $params = [];

        if ($filtro !== null && $valor !== null) {
            if ($filtro === 'nombre_empresa' || $filtro === 'pais') {
                $sql .= " WHERE $filtro LIKE ?";
                $params[] = "%" . $valor . "%";
            } else if ($filtro === 'valoracion') {
                $sql .= " WHERE $filtro = ?";
                $params[] = $valor;
            }
        }

        if ($orderBy !== null) {
            $sql .= " ORDER BY $orderBy $dir";
        }

        if ($page !== null && $limit !== null) {
            $offset = ($page - 1) * $limit;
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $query = $pdo->prepare($sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    public function delete($id){
        try {
            $pdo = $this->crearConexion();
            $query = $pdo->prepare("DELETE FROM editor WHERE id_editor = ?");
            $query->execute([$id]);

            return "success";
        } catch (PDOException $e) {
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451) {
                return "restricted";
            }

            return "error";
        }
    }

    public function insert($nombreEmpresa, $pais, $sitioWeb, $valoracion, $descripcion, $imagen){

        try {
            $pdo = $this->crearConexion();
            $query = $pdo->prepare("INSERT INTO editor (nombre_empresa, pais, sitio_web, valoracion, descripcion, imagen) VALUES (?, ?, ?, ?, ?, ?)");
            $query->execute([$nombreEmpresa, $pais, $sitioWeb, $valoracion, $descripcion, $imagen]);
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update($id, $nombreEmpresa, $pais, $sitioWeb, $valoracion, $descripcion, $imagen){
        try {
            $pdo = $this->crearConexion();
            $query = $pdo->prepare("UPDATE editor SET nombre_empresa = ?, pais = ?, sitio_web = ?, valoracion = ?, descripcion = ?, imagen = ? WHERE id_editor = ?");
            $query->execute([$nombreEmpresa, $pais, $sitioWeb, $valoracion, $descripcion, $imagen, $id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
