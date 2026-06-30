<?php
require_once 'app/models/game.model.php';

class GameController
{

    private $model;

    function __construct()
    {
        $this->model = new ModelGame();
    }

    function getGame($request, $response)
    {

        $idGame = $request->params->id;
        $game = $this->model->get($idGame);

        if (!$game) {
            return $response->jasn("El juego con el id=$idGame no existe en la base de datos", 404);
        }
        $response->json($game, 200);
    }
    
    function getGames($request, $response)
    {

        $campoFiltro = $request->query->filtrar_por ?? null;
        $valorFiltro = $request->query->valor ?? null;

        $columnasFiltroPermitidas = ['titulo', 'precio', 'valoracion', 'fecha_lanzamiento', 'id_editor', 'nombre_editor'];

        if ($campoFiltro !== null && !in_array($campoFiltro, $columnasFiltroPermitidas)) {
            return $response->json(["msg" => "El campo de filtrado '$campoFiltro' no es valido"], 400);
        }

        $orderBy = $request->query->order_by ?? null;
        $orderDir = $request->query->order_dir ?? 'ASC';

        $columnasOrdenPermitidas = ['id_videojuego', 'titulo', 'precio', 'valoracion', 'fecha_lanzamiento', 'id_editor'];

        if ($orderBy !== null && !in_array($orderBy, $columnasOrdenPermitidas)) {
            return $response->json(["msg" => "El campo de ordenamiento '$orderBy' no es valido"], 400);
        }

        $page = $request->query->page ?? null;
        $limit = $request->query->limit ?? null;

        if ($page !== null) {
            $page = (int)$page;
            if ($page < 1) $page = 1;
        }

        if ($limit !== null) {
            $limit = (int)$limit;
            if ($limit < 1) $limit = 5;
        }

        $juegos = $this->model->getAll($campoFiltro, $valorFiltro, $orderBy, $orderDir, $page, $limit);

        return $response->json($juegos, 200);
    }

    function createGame($request, $response)
    {
        if (
            empty($request->body->titulo) || empty($request->body->descripcion) ||
            empty($request->body->precio) || empty($request->body->fecha_lanzamiento) ||
            empty($request->body->id_editor)
        ) {
            return $response->json(["msg" => "Faltan datos obligatorios para crear el videojuego"], 400);
        }

        $titulo = $request->body->titulo;
        $precio = $request->body->precio;
        $fechaLanzamiento = $request->body->fecha_lanzamiento;
        $idEditor = (int)$request->body->id_editor;
        
        $valoracion = $request->body->valoracion ?? null;
        $descripcion = $request->body->descripcion ?? null;
        $resenia = $request->body->resenia ?? null;
        $imagen = $request->body->imagen ?? null;

        $modelEditor = new ModelEditor();
        $editorExiste = $modelEditor->get($idEditor);

        if (!$editorExiste) {
            return $response->json(["msg" => "El id_editor ($idEditor) provisto no corresponde a ningun editor existente"], 400);
        }

        $idNuevoJuego = $this->model->insert($titulo, $descripcion, $precio, $resenia, $valoracion, $fechaLanzamiento, $imagen, $idEditor);

        if (!$idNuevoJuego) {
            return $response->json(["msg" => "No se pudo crear el videojuego. Puede que el titulo ya exista."], 400);
        }

        $nuevoJuego = $this->model->get($idNuevoJuego);
        return $response->json($nuevoJuego, 201);
    }

    function deleteGame($request, $response)
    {
        $idGame = $request->params->id;
        $game = $this->model->get($idGame);

        if (!$game) {
            return $response->json("El juego con el id=$idGame no existe", 404);
        }

        $this->model->delete($idGame);
        
        return $response->json("El juego con el id=$idGame se eliminó correctamente", 204);
    }

    function updateGame($request, $response)
    {
        $idJuego = $request->params->id;
        
        $juego = $this->model->get($idJuego);
        if (!$juego) {
            return $response->json("El videojuego con el id=$idJuego no existe en la base de datos", 404);
        }

        if (empty($request->body->titulo) || empty($request->body->precio) || 
            empty($request->body->fecha_lanzamiento) || empty($request->body->id_editor)) {
            return $response->json("Faltan datos obligatorios (titulo, precio, fecha o id_editor)", 400);
        }

        $titulo = $request->body->titulo;
        $precio = $request->body->precio;
        $fechaLanzamiento = $request->body->fecha_lanzamiento;
        $idEditor = (int)$request->body->id_editor;

        $valoracion = $request->body->valoracion ?? null;
        $descripcion = $request->body->descripcion ?? null;
        $resenia = $request->body->resenia ?? null;
        $imagen = $request->body->imagen ?? null;

        $modelEditor = new ModelEditor();
        if (!$modelEditor->get($idEditor)) {
            return $response->json("El id_editor ($idEditor) no corresponde a ningun editor existente", 400);
        }

        $this->model->update($idJuego, $titulo, $descripcion, $precio, $resenia, $valoracion, $fechaLanzamiento, $imagen, $idEditor);

        $juegoActualizado = $this->model->get($idJuego);
        return $response->json($juegoActualizado, 200);
    }


}
