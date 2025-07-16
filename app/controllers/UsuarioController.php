<?php
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    public function obtenerTodos(): array
    {
        return $this->usuarioModel->listarTodos();
    }

    public function obtenerPorId(int $id): ?array
    {
        return $this->usuarioModel->listarPorId($id);
    }

    public function crear(array $data): int
    {
        // Validación mínima
        if (empty($data['nombre']) || empty($data['email'])) {
            throw new Exception("Nombre y correo son obligatorios.");
        }

        return $this->usuarioModel->guardarUsuario($data);
    }

    public function actualizar(array $data): bool
    {
        return $this->usuarioModel->actualizarUsuario($data);
    }

    public function eliminar(int $id): bool
    {
        return $this->usuarioModel->borrarUsuario($id);
    }
}
