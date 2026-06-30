<?php
require_once 'app/models/editor.model.php';

class EditorController
{

    private $model;

    function __construct()
    {
        $this->model = new ModelEditor();
    }

    function getEditor($request, $response)
    {
        $idEditor = $request->params->id;
        $editor = $this->model->get($idEditor);

        if (!$editor) {
            return $response->json(["msj" => "El editor con el id=$idEditor no existe en la base de datos"], 404);
        }
        return $response->json($editor, 200);
    }

    function getEditors($request, $response)
    {

        $campoFiltro = $request->query->filtrar_por ?? null;
        $valorFiltro = $request->query->valor ?? null;

        $columnasFiltroPermitidas = ['nombre_empresa', 'pais', 'valoracion'];
        if ($campoFiltro !== null && !in_array($campoFiltro, $columnasFiltroPermitidas)) {
            $response->json(["msg" => "El campo de filtrado '$campoFiltro' no es valido"], 400);
            return;
        }

        $orderBy = $request->query->order_by ?? null;
        $orderDir = $request->query->order_dir ?? 'ASC';

        $columnasOrdenPermitidas = ['id_editor', 'nombre_empresa', 'pais', 'sitio_web', 'valoracion'];
        if ($orderBy !== null && !in_array($orderBy, $columnasOrdenPermitidas)) {
            $response->json(["msg" => "El campo de ordenamiento '$orderBy' no es valido"], 400);
            return;
        }

        $page = $request->query->page ?? null;
        $limit = $request->query->limit ?? null;

        if ($page !== null) {
            $page = (int)$page;
            if ($page < 1) {
                $page = 1;
            }
        }

        if ($limit !== null) {
            $limit = (int)$limit;
            if ($limit < 1) {
                $limit = 5;
            }
        }

        $editores = $this->model->getAll($campoFiltro, $valorFiltro, $orderBy, $orderDir, $page, $limit);

        $response->json($editores, 200);
    }

    function createEditor($request, $response)
    {

        if (empty($request->body->nombre_empresa) || empty($request->body->pais) || empty($request->body->sitio_web)) {
            return $response->json(["msg" => "Faltan datos obligatorios (nombre_empresa, pais o sitio_web)"], 400);
        }

        $nombreEmpresa = $request->body->nombre_empresa;
        $pais          = $request->body->pais;
        $sitioWeb      = $request->body->sitio_web;

        $descripcion   = $request->body->descripcion ?? null;
        $valoracion    = $request->body->valoracion ?? null;
        $imagen        = $request->body->imagen ?? null;

        $idNuevo = $this->model->insert($nombreEmpresa, $pais, $sitioWeb, $valoracion, $descripcion, $imagen);

        if (!$idNuevo) {
            return $response->json(["msg" => "No se pudo crear el editor"], 500);
        }

        $nuevoEditor = $this->model->get($idNuevo);
        return $response->json($nuevoEditor, 201);
    }

    public function deleteEditor($request, $response)
    {
        $idEditor = $request->params->id;
        $editor = $this->model->get($idEditor);

        if (!$editor) {
            return $response->json("El Editor con el id=$idEditor no existe", 404);
        }

        $status = $this->model->delete($idEditor);
        if ($status === "restricted") {
            return $response->json(["msg" => "No se puede eliminar el editor porque tiene videojuegos asociados"], 400);
        }
        return $response->json("El editor con el id=$idEditor se eliminó correctamente", 204);
    }

    function updateEditor($request, $response)
    {
        $idEditor = $request->params->id;

        $editor = $this->model->get($idEditor);
        if (!$editor) {
            return $response->json(["msg" => "El editor con el id=$idEditor no existe en la base de datos"], 404);
        }

        if (empty($request->body->nombre_empresa) || empty($request->body->pais) || empty($request->body->sitio_web)) {
            return $response->json(["msg" => "Faltan datos obligatorios (nombre_empresa, pais o sitio_web)"], 400);
        }

        $nombreEmpresa = $request->body->nombre_empresa;
        $pais          = $request->body->pais;
        $sitioWeb      = $request->body->sitio_web;

        $descripcion   = $request->body->descripcion ?? null;
        $valoracion    = $request->body->valoracion ?? null;
        $imagen        = $request->body->imagen ?? null;

        $this->model->update($idEditor, $nombreEmpresa, $pais, $sitioWeb, $valoracion, $descripcion, $imagen);

        $editorActualizado = $this->model->get($idEditor);
        return $response->json($editorActualizado, 200);
    }
}
