<?php

class TaskController extends Controller
{
    private JsonHandler $jsonHandlerUsuarios;

    public function __construct()
    {
        $this->jsonHandlerUsuarios = new JsonHandler(ROOT_PATH . '/data/usuarios.json');
    }

    public function indexAction()
    {
        $tasks = Task::getAll();
        $this->view->tasks = $tasks;
        $this->view->message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
    }

    public function showAction()
    {
        $id_tarea = $_GET['id'] ?? null;
        if (!$id_tarea) {
            $this->redirectWithMessage('../tasks', 'Error: ID de tarea no especificado.');
        }

        $task = Task::findById($id_tarea);
        if (!$task) {
            $this->redirectWithMessage('../tasks', 'Error: Tarea no encontrada.');
        }

        $this->view->task = $task;
    }

    public function createAction()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $usuariosArr = $this->getUsuarios();
            $this->view->usuariosData = $usuariosArr;

            if (!isset($this->view->data) || !is_array($this->view->data)) {
                $this->view->data = [];
            }
            $this->view->usuarioSeleccionado = $this->view->data['usuario_id'] ?? null;

            $this->view->task = null;
            $this->view->error = null;
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $success = $this->processCreate();
            if ($success) {
                return;
            }
        }
    }

    public function editAction()
    {
        $id_tarea = $_POST['id_tarea'] ?? $_GET['id'] ?? null;
        if (!$id_tarea) {
            $this->redirectWithMessage('../tasks', 'Error: ID de tarea no especificado.');
        }

        $task = Task::findById($id_tarea);
        if (!$task) {
            $this->redirectWithMessage('../tasks', 'Error: Tarea no encontrada.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->view->task = $task;
            $this->view->error = null;
            $this->view->usuariosData = $this->getUsuarios();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $success = $this->processEdit($task);
            if ($success) {
                return; // redirect dentro de processEdit
            }
            // En caso de error dentro de processEdit
            $this->view->usuariosData = $this->getUsuarios();
        }
    }

    public function deleteAction()
    {
        $id_tarea = $_POST['id_tarea'] ?? $_GET['id'] ?? null;
        if (!$id_tarea) {
            $this->redirectWithMessage('/tasks', 'Error: ID de tarea no especificado.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $task = Task::findById($id_tarea);
            if (!$task) {
                $this->redirectWithMessage('../tasks', 'Error: Tarea no encontrada.');
            }
            $this->view->task = $task;
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $confirm = $_POST['confirm_delete'] ?? null;
            $checkbox = $_POST['confirm_checkbox'] ?? null;

            if ($confirm && $checkbox) {
                if (Task::deleteTask($id_tarea)) {
                    $this->redirectWithMessage('../tasks', 'Tarea eliminada exitosamente.');
                } else {
                    $this->redirectWithMessage('../tasks', 'Error al eliminar la tarea.');
                }
            } else {
                $this->redirectWithMessage('../tasks/delete?id=' . $id_tarea, 'Debe confirmar correctamente para eliminar.');
            }
        }
    }

    private function processCreate()
    {
        $data = [
            'titulo' => trim($_POST['titulo'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'estado' => $_POST['estado'] ?? 'pendiente',
            'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
            'fecha_fin' => $_POST['fecha_fin'] ?? '',
            'usuario_id' => (int)($_POST['usuario_id'] ?? 1),
        ];

        if ($data['titulo'] === '') {
            $this->view->error = 'El título es obligatorio.';
            $this->view->data = $data;
            $this->view->usuariosData = $this->getUsuarios();
            $this->view->usuarioSeleccionado = $data['usuario_id'];
            $this->view->task = null;
            return false;
        }

        $task = new Task($data);
        if ($task->saveTask()) {
            $this->redirectWithMessage('../tasks', 'Tarea creada exitosamente.');
        }

        $this->view->error = 'Error al crear la tarea.';
        $this->view->data = $data;
        $this->view->usuariosData = $this->getUsuarios();
        $this->view->usuarioSeleccionado = $data['usuario_id'];
        $this->view->task = null;
        return false;
    }

    private function processEdit(Task $task)
    {
        $data = [
            'titulo' => trim($_POST['titulo'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'estado' => $_POST['estado'] ?? 'pendiente',
            'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
            'fecha_fin' => $_POST['fecha_fin'] ?? '',
            'usuario_id' => (int)($_POST['usuario_id'] ?? 1),
        ];

        if ($data['titulo'] === '') {
            $this->view->task = $task;
            $this->view->error = 'El título es obligatorio.';
            return false;
        }

        $task->titulo = $data['titulo'];
        $task->descripcion = $data['descripcion'];
        $task->estado = TaskStatus::from($data['estado']);
        $task->fecha_inicio = $data['fecha_inicio'];
        $task->fecha_fin = $data['fecha_fin'];
        $task->usuario_id = $data['usuario_id'];

        if ($task->update()) {
            $this->redirectWithMessage('../tasks', 'Tarea actualizada exitosamente.');
        } else {
            $this->view->task = $task;
            $this->view->error = 'Error al actualizar la tarea.';
            return false;
        }
    }

    private function getUsuarios(): array
    {
        $usuariosArr = $this->jsonHandlerUsuarios->leer();
        return is_array($usuariosArr) ? $usuariosArr : [];
    }

    private function redirectWithMessage(string $url, ?string $message = null): void
    {
        if ($message !== null) {
            $_SESSION['flash_message'] = $message;
        }
        header('Location: ' . $url);
        exit;
    }
}
