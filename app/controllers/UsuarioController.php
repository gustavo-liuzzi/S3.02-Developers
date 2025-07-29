<?php

class UsuarioController extends Controller
{
    private Usuario $usuarioModel;
    public array $usuarios;

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
        $_SESSION['flash_message'] = "Usuario creado correctamente con ID: " . $resultado;
        header("Location: " . WEB_ROOT . "/");
        exit;
    } else {
        $_SESSION['flash_message'] = "Error al guardar el usuario.";
        header("Location: " . WEB_ROOT . "/");
        exit;
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

        foreach ($ids as $id) {
            $this->usuarioModel->borrarUsuario($id);
        }

        header("Location: " . WEB_ROOT . "/modules");
        exit;
    }

public function editarvariosAction(): void
{
    $ids = $_POST['seleccionados'] ?? [];

    if (empty($ids)) {
        echo "<script>
            alert('Debes seleccionar al menos un usuario.');
            window.location.href = '" . WEB_ROOT . "/modules';
        </script>";
        exit;
    }

    $usuarios = [];

    foreach ($ids as $id) {
        $usuario = $this->usuarioModel->listarPorId((int)$id);
        if ($usuario) {
            $usuarios[] = $usuario;
        }
    }

    $this->view->usuarios = $usuarios;

}


    public function actualizarvariosAction(): void
    {
        $data = $_POST['usuarios'] ?? [];

        foreach ($data as $usuario) {
            $this->usuarioModel->actualizarUsuario($usuario);
        }
        $_SESSION['flash_message'] = "Los datos han sido modificados";
        header("Location: " . WEB_ROOT . "/modules");
        exit;
    }
}
