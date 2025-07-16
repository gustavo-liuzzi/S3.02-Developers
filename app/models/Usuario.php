<?php

declare(strict_types=1);

class Usuario extends Model
{
    private JsonHandler $jsonHandler;

    public function __construct()
    {
        $this->jsonHandler = new JsonHandler(ROOT_PATH . '/data/usuarios.json');
    }

    public function listarTodos(): array
    {
        return $this->jsonHandler->leer();
    }

    public function listarPorId(int $id): ?array
    {
        $usuarios = $this->jsonHandler->leer();
        foreach ($usuarios as $usuario) {
            if (isset($usuario['id']) && (int)$usuario['id'] === $id) {
                return $usuario;
            }
        }
        return null;
    }

    /* añado función para comprobar si el usuario (email) ya existe
    pero no funciona, a pesar de ser llamada desde UsuarioController
    correctamente, a investigar por qué */

    public function guardarUsuario(array $data): int
    {
        $usuarios = $this->jsonHandler->leer();

        foreach ($usuarios as $usuario) {
            if (isset($usuario['email']) && strtolower($usuario['email']) === strtolower($data['email'])) {
                throw new Exception("Error: El usuario ya existe");
            }
        }

        if (count($usuarios) > 0) {
            $ultimoId = end($usuarios)['id'];
            $data['id'] = $ultimoId + 1;
        } else {
            $data['id'] = 1;
        }

        $usuarios[] = $data;
        $this->jsonHandler->guardar($usuarios);

        return $data['id'];
    }


    public function actualizarUsuario(array $data): bool
    {
        if (empty($data['id'])) {
            throw new Exception("El ID es obligatorio para la actualización.");
        }

        $usuarios = $this->jsonHandler->leer();
        $id = (int)$data['id'];
        $actualizado = false;

        foreach ($usuarios as &$usuario) {
            if ((int)$usuario['id'] === $id) {
                foreach ($data as $campo => $valor) {
                    if ($campo !== 'id') {
                        $usuario[$campo] = $valor;
                    }
                }
                $actualizado = true;
                break;
            }
        }

        if ($actualizado) {
            $this->jsonHandler->guardar($usuarios);
        }

        return $actualizado;
    }

    public function borrarUsuario(int $id): bool
    {
        $usuarios = self::listarTodos();
        $borrado = false;
        foreach ($usuarios as $indice => $usuario) {
            if ((int)$usuario['id'] === $id) {
                unset($usuarios[$indice]);
                $borrado = true;
                break;
            }
        }

        if ($borrado) {
            $this->jsonHandler->guardar($usuarios);
        }
        return $borrado;
    }
}
