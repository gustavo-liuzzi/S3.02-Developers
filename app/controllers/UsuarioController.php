<?php

class UsuarioController extends Controller
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    public function obtenerTodosAction(): array
    {
        return $this->usuarioModel->listarTodos();
    }

    public function obtenerPorIdAction(int $id): ?array
    {
        return $this->usuarioModel->listarPorId($id);
    }

    public function crearAction(): void
    {
        $data = $this->_getAllParams();
        $resultado = $this->usuarioModel->guardarUsuario($data);

        if ($resultado) {
            $this->redirectWithMessage(WEB_ROOT . "/", "Usuario creado correctamente con ID: " . $resultado);
        } else {
            $this->redirectWithMessage(WEB_ROOT . "/", "Error al guardar el usuario.");
        }
    }

    public function actualizarAction(array $data): bool
    {
        return $this->usuarioModel->actualizarUsuario($data);
    }

    public function eliminarAction(int $id): bool
    {
        return $this->usuarioModel->borrarUsuario($id);
    }

    public function eliminarvariosAction(): void
    {
        $ids = $_POST['seleccionados'] ?? [];
        if (!is_array($ids) || empty($ids)) {
            $this->redirectWithMessage(WEB_ROOT . "/modules");
        }

        $ids = array_map('intval', $ids);
        $this->usuarioModel->borrarVarios($ids);

        $tareas = Task::getAll();
        $tareasFiltradas = array_filter($tareas, fn($tarea) => !in_array($tarea->usuario_id, $ids));
        Task::saveAll(array_values($tareasFiltradas));

        $this->redirectWithMessage(WEB_ROOT . "/modules", "Usuarios eliminados correctamente.");
    }

    public function editarvariosAction(): void
    {
        $ids = $_POST['seleccionados'] ?? [];

        if (!is_array($ids) || empty($ids)) {
            $this->redirectWithMessage(WEB_ROOT . "/modules", "Debes seleccionar al menos un usuario.");
        }

        // Convertir todos a enteros y filtrar usuarios que existan
        $ids = array_map('intval', $ids);
        $usuarios = array_filter(array_map(fn($id) => $this->usuarioModel->listarPorId($id), $ids));

        $this->view->usuarios = $usuarios;
    }

    public function actualizarvariosAction(): void
    {
        $data = $_POST['usuarios'] ?? [];

        if (!is_array($data) || empty($data)) {
            $this->redirectWithMessage(WEB_ROOT . "/modules", "No se recibieron datos para actualizar.");
        }

        foreach ($data as $usuario) {
            $this->usuarioModel->actualizarUsuario($usuario);
        }

        $this->redirectWithMessage(WEB_ROOT . "/modules", "Los datos han sido modificados.");
    }

    public function tareasAction(): void
    {
        $id = $this->obtenerIdDesdeRuta();
        if (is_null($id)) {
            http_response_code(404);
            echo "Usuario no encontrado (ID no especificado)";
            exit;
        }

        $usuario = $this->usuarioModel->listarPorId($id);
        if (!$usuario) {
            http_response_code(404);
            echo "Usuario no encontrado";
            exit;
        }

        $tareas = Task::findByUsuarioId($id);

        $this->view->usuario = $usuario;
        $this->view->tareas = $tareas;
    }

    /**
     * Obtiene el ID numérico del usuario desde la URI
     */
    private function obtenerIdDesdeRuta(): ?int
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (preg_match('#/usuarios/(\d+)$#', $uri, $matches)) {
            return (int)$matches[1];
        }
        return null;
    }

    private function redirectWithMessage(string $url, ?string $message = null): void
    {
        if ($message) {
            $_SESSION['flash_message'] = $message;
        }
        header("Location: $url");
        exit;
    }
}
