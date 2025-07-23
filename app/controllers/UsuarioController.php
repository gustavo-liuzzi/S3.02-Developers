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

        if (empty($data['nombre']) || empty($data['email']) || empty($data['password'])) {
            throw new Exception("Todos los campos son obligatorios.");
        }

        $resultado = $this->usuarioModel->guardarUsuario($data);

        if ($resultado) {
            echo "<script>
            alert('Usuario creado correctamente con ID: " . $resultado . "');
            window.location.href = '" . WEB_ROOT . "/';
        </script>";
            exit;
        } else {
            echo "<script>
            alert('Error al guardar el usuario');
            history.back();
        </script>";
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
}
