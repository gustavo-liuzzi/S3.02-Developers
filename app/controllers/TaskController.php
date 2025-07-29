<?php

require_once __DIR__ . '/../models/Task.php';

class TaskController extends ApplicationController {

    private function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
    // Listar todas las tareas (READ)
    public function indexAction() {
        $tasks = Task::getAll();
        $this->view->tasks = $tasks;
        $this->view->message = $_GET['message'] ?? null;
    }
    
    // Mostrar una tarea específica (READ)
    public function showAction() {
        $id_tarea = $_GET['id'] ?? null;
        
        if (!$id_tarea) {
            $this->redirect('/tasks?message=' . urlencode('Error: ID de tarea no especificado.'));
            return;
        }
        
        $task = Task::findById($id_tarea);
        
        if (!$task) {
            $this->redirect('/tasks?message=' . urlencode('Error: Tarea no encontrada.'));
            return;
        }
        
        $this->view->task = $task;
    }
    
    // Mostrar formulario para crear nueva tarea (CREATE)
    public function createAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Mostrar formulario vacío
            $this->view->task = null;
            $this->view->error = null;
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Procesar formulario
            $this->processCreate();
        }
    }
    
    // Mostrar formulario para editar tarea existente (UPDATE)
    public function editAction() {
        $id_tarea = $_GET['id'] ?? null;
        
        if (!$id_tarea) {
            $this->redirect('/tasks?message=' . urlencode('Error: ID de tarea no especificado.'));
            return;
        }
        
        $task = Task::findById($id_tarea);
        
        if (!$task) {
            $this->redirect('/tasks?message=' . urlencode('Error: Tarea no encontrada.'));
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->view->task = $task;
            $this->view->error = null;
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processEdit($task);
        }
    }
    
    
    
    // Eliminar una tarea (DELETE)
    public function deleteAction() {
        $id_tarea = $_GET['id'] ?? null;
        
        if (!$id_tarea) {
            $this->redirect('/tasks?message=' . urlencode('Error: ID de tarea no especificado.'));
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Mostrar vista de confirmación (delete.phtml se carga automáticamente)
            // No necesitas hacer nada más, la vista carga la tarea directamente
            
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Procesar eliminación
            $confirm = $_POST['confirm_delete'] ?? null;
            $checkbox = $_POST['confirm_checkbox'] ?? null;
            
            if ($confirm && $checkbox) {
                if (Task::delete($id_tarea)) {
                    $this->redirect('/tasks?message=' . urlencode('Tarea eliminada exitosamente.'));
                } else {
                    $this->redirect('/tasks?message=' . urlencode('Error al eliminar la tarea.'));
                }
            } else {
                $this->redirect('/tasks/delete?id=' . $id_tarea . '&error=1');
            }
        }
    }
    
    
    // Procesar la creación de una nueva tarea (privado)
    private function processCreate() {
        $data = [
            'titulo' => $_POST['titulo'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'estado' => $_POST['estado'] ?? 'pendiente',
            'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
            'fecha_fin' => $_POST['fecha_fin'] ?? '',
            'usuario_id' => $_POST['usuario_id'] ?? 1
        ];
        
        // Validación básica
        if (empty($data['titulo'])) {
            $this->view->error = 'El título es obligatorio.';
            $this->view->data = $data;
            return;
        }
        
        // Crear y guardar la tarea
        $task = new Task($data);
        if ($task->save()) {
            $this->redirect('/tasks?message=' . urlencode('Tarea creada exitosamente.'));
        } else {
            $this->view->error = 'Error al crear la tarea.';
            $this->view->data = $data;
        }
    }
    
    // Procesar la edición de una tarea (privado)
    private function processEdit($task) {
        $data = [
            'titulo' => $_POST['titulo'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'estado' => $_POST['estado'] ?? 'pendiente',
            'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
            'fecha_fin' => $_POST['fecha_fin'] ?? '',
            'usuario_id' => $_POST['usuario_id'] ?? 1
        ];
        
        // Validación básica
        if (empty($data['titulo'])) {
            $this->view->task = $task;
            $this->view->error = 'El título es obligatorio.';
            return;
        }
        
        // Actualizar propiedades
        $task->titulo = $data['titulo'];
        $task->descripcion = $data['descripcion'];
        $task->estado = TaskStatus::from($data['estado']);
        $task->fecha_inicio = $data['fecha_inicio'];
        $task->fecha_fin = $data['fecha_fin'];
        $task->usuario_id = $data['usuario_id'];
        
        // Guardar cambios
        if ($task->update()) {
            $this->redirect('/tasks?message=' . urlencode('Tarea actualizada exitosamente.'));
        } else {
            $this->view->task = $task;
            $this->view->error = 'Error al actualizar la tarea.';
        }
    }

    
}
