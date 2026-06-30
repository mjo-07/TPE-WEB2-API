<?php
require_once 'Model.php';

class ModelGame extends Model
{


    function get($id)
    {
        $pdo = $this->crearConexion();
        $query = $pdo->prepare("SELECT v.*, e.id_editor, e.nombre_empresa AS nombre_editor FROM video_juego v JOIN editor e USING (id_editor) WHERE id_juego = ?");
        $query->execute([$id]);
        $game = $query->fetch(PDO::FETCH_OBJ);
        return $game;
    }

    function getAll($filtro, $valor, $orderBy, $dir, $page, $limit)
    {
        $pdo = $this->crearConexion();

        $sql = "SELECT v.*, e.nombre_empresa AS nombre_editor FROM video_juego v JOIN editor e USING(id_editor)";
        $params = [];

        if ($filtro !== null && $valor !== null) {
            if ($filtro === 'titulo') {
                $sql .= " WHERE v.titulo LIKE ?";
                $params[] = "%" . $valor . "%";
            } else if ($filtro === 'nombre_editor') {
                $sql .= " WHERE e.nombre_empresa LIKE ?";
                $params[] = "%" . $valor . "%";
            } else if ($filtro === 'precio' || $filtro === 'valoracion' || $filtro === 'id_editor') {
                $sql .= " WHERE v.$filtro = ?";
                $params[] = $valor;
            }
        }

        if ($orderBy !== null) {
            if ($orderBy === 'id_editor' || $orderBy === 'valoracion') {
                $sql .= " ORDER BY v.$orderBy $dir";
            } else {
                $sql .= " ORDER BY $orderBy $dir";
            }
        }

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
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    function insert($titulo, $descripcion, $precio, $resenia, $valoracion, $fechaLanzamiento, $imagen, $idEditor)
    {
        try {
            $pdo = $this->crearConexion();
            $query = $pdo->prepare("
                INSERT INTO video_juego (titulo, descripcion, precio, resenia, valoracion, fecha_lanzamiento, imagen, id_editor) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $query->execute([$titulo, $descripcion, $precio, $resenia, $valoracion, $fechaLanzamiento, $imagen, $idEditor]);

            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    function update($id, $titulo, $descripcion, $precio, $resenia, $valoracion, $fechaLanzamiento, $imagen, $idEditor)
    {
        try {
            $pdo = $this->crearConexion();
            $query = $pdo->prepare("UPDATE video_juego SET titulo = ?, descripcion = ?, precio = ?, resenia = ?, valoracion = ?, fecha_lanzamiento = ?, imagen = ?, id_editor = ? WHERE id_juego = ?");
            return $query->execute([$titulo, $descripcion, $precio, $resenia, $valoracion, $fechaLanzamiento, $imagen, $idEditor, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
