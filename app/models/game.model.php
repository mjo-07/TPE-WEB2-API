<?php
require_once 'Model.php';

class ModelGame extends Model
{


    function get($id)
    {
        $pdo = $this->crearConexion();
        $query = $pdo->prepare("SELECT v.*, e.id_editor, e.nombre_empresa FROM video_juego v JOIN editor e USING (id_editor) WHERE id_juego = ?");
        $query->execute([$id]);
        $game = $query->fetch(PDO::FETCH_OBJ);
        return $game;
    }

    function getAll($filtro, $valor, $orderBy, $dir, $page, $limit)
    {
        $pdo = $this->crearConexion();

        $sql = "SELECT v.*, e.nombre_empresa FROM video_juego v JOIN editor e USING(id_editor)";
        $params = [];

        // 1. Filtros
        if ($filtro !== null && $valor !== null) {
            if ($filtro === 'titulo') {
                $sql .= " WHERE v.titulo LIKE ?";
                $params[] = "%" . $valor . "%";
            } else if ($filtro === 'precio' || $filtro === 'valoracion' || $filtro === 'id_editor') {
                $sql .= " WHERE v.$filtro = ?";
                $params[] = $valor;
            }
        }

        // 2. Ordenamiento
        if ($orderBy !== null) {
            // Validamos contra ambigüedad si ordenan por id_editor o valoracion
            if ($orderBy === 'id_editor' || $orderBy === 'valoracion') {
                $sql .= " ORDER BY v.$orderBy $dir";
            } else {
                $sql .= " ORDER BY $orderBy $dir";
            }
        }

        // 3. Paginación
        if ($page !== null && $limit !== null) {
            $offset = ($page - 1) * $limit;
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $query = $pdo->prepare($sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_OBJ);
    }



    function getGamesDestacados($valoracion)
    {
        $pdo = $this->crearConexion();
        $query = $pdo->prepare("SELECT titulo AS nombre, imagen, id_juego AS id FROM video_juego WHERE valoracion = ? LIMIT 3");
        $query->execute([$valoracion]);
        $destacados = $query->fetchAll(PDO::FETCH_OBJ);
        return $destacados;
    }

    function getGamesByEditor($idEditor)
    {
        $pdo = $this->crearConexion();
        $query = $pdo->prepare("SELECT * FROM video_juego WHERE id_editor = ?");
        $query->execute([$idEditor]);
        $games = $query->fetchAll(PDO::FETCH_OBJ);
        return $games;
    }

    function delete($id)
    {
        try {
            $pdo = $this->crearConexion();
            $query = $pdo->prepare("DELETE FROM video_juego WHERE id_juego = ?");
            $query->execute([$id]);

            return true; // Se eliminó correctamente

        } catch (PDOException $e) {
            return false; // Ocurrió un error inesperado
        }
    }

    function insert($titulo, $descripcion, $precio, $resenia, $fechaLanzamiento, $imagen, $idEditor)
    {
        try {
            $pdo = $this->crearConexion();
            $query = $pdo->prepare("
                INSERT INTO video_juego (titulo, descripcion, precio, resenia, fecha_lanzamiento, imagen, id_editor) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $query->execute([$titulo, $descripcion, $precio, $resenia, $fechaLanzamiento, $imagen, $idEditor]);

            // Devolvemos el ID generado por el autoincremental
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    //function update($id, $nombre, $precio, $lanzamiento, $valoracion, $id_editor, $descripcion, $resenia, $imagen)
    //{
    //    try {
    //        $pdo = $this->crearConexion();
    //        $query = $pdo->prepare("UPDATE video_juego SET titulo = ?, precio = ?, fecha_lanzamiento = ?, valoracion = ?, id_editor = ?, descripcion = ?, resenia = ?, imagen = ? WHERE id_juego = ?");
    //        return $query->execute([$nombre, $precio, $lanzamiento, $valoracion, $id_editor, $descripcion, $resenia, $imagen, $id]);
    //    } catch (PDOException $e) {
    //        return false;
    //    }
    //}

    function update($id, $titulo, $descripcion, $precio, $resenia, $fechaLanzamiento, $imagen, $idEditor)
    {
        try {
            $pdo = $this->crearConexion();
            $query = $pdo->prepare("UPDATE video_juego SET titulo = ?, descripcion = ?, precio = ?, resenia = ?, fecha_lanzamiento = ?, imagen = ?, id_editor = ? WHERE id_juego = ?");
            return $query->execute([$titulo, $descripcion, $precio, $resenia, $fechaLanzamiento, $imagen, $idEditor, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
