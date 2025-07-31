<?php

enum TaskStatus: string {
    case PENDIENTE = "pendiente";
    case EN_EJECUCION = "en ejecucion";
    case FINALIZADA = "finalizada";
}

class Task extends Model
{
    private JsonHandler $jsonHandler;

    public $id_tarea;
    public $titulo;
    public $descripcion;
    public TaskStatus $estado;
    public $fecha_inicio;
    public $fecha_fin;
    public $usuario_id;

    public function __construct($data = [])
    {
        $this->jsonHandler = new JsonHandler(ROOT_PATH . '/data/tasks.json');

        $this->id_tarea = $data["id_tarea"] ?? null;
        $this->titulo = $data["titulo"] ?? "";
        $this->descripcion = $data["descripcion"] ?? "";
        $this->estado = isset($data["estado"]) ? TaskStatus::from($data["estado"]) : TaskStatus::PENDIENTE;
        $this->fecha_inicio = $data["fecha_inicio"] ?? "";
        $this->fecha_fin = $data["fecha_fin"] ?? "";
        $this->usuario_id = $data["usuario_id"] ?? null;
    }

    public static function getAll()
    {
        $instance = new self();
        $tasksArr = $instance->jsonHandler->leer() ?: [];
        return array_map(fn($t) => new self($t), $tasksArr);
    }

    public static function findById($id_tarea)
    {
        $tasks = self::getAll();
        foreach ($tasks as $task) {
            if ((int)$task->id_tarea === (int)$id_tarea) {
                return $task;
            }
        }
        return null;
    }

    public function saveTask()
    {
        $tasks = self::getAll();
        if (!$this->id_tarea) {
            $this->id_tarea = count($tasks) ? max(array_map(fn($t) => $t->id_tarea, $tasks)) + 1 : 1;
        }
        $tasks[] = $this;
        self::saveAll($tasks);
        return true;
    }

    public function update()
    {
        $tasks = self::getAll();
        foreach ($tasks as $index => $task) {
            if ($task->id_tarea == $this->id_tarea) {
                $tasks[$index] = $this;
                break;
            }
        }
        self::saveAll($tasks);
        return true;
    }

    public static function deleteTask($id_tarea)
    {
        $tasks = self::getAll();
        $tasks = array_filter($tasks, fn($task) => $task->id_tarea != $id_tarea);
        self::saveAll($tasks);
        return true;
    }

    /**
     * Ahora es público para que se use fuera, igual que en Usuario.
     */
    public static function saveAll(array $tasks)
    {
        $instance = new self();
        $arr = array_map(fn($t) => $t->toArray(), $tasks);
        $instance->jsonHandler->guardar($arr);
    }

    public function toArray()
    {
        return [
            "id_tarea" => $this->id_tarea,
            "titulo" => $this->titulo,
            "descripcion" => $this->descripcion,
            "estado" => $this->estado->value,
            "fecha_inicio" => $this->fecha_inicio,
            "fecha_fin" => $this->fecha_fin,
            "usuario_id" => $this->usuario_id,
        ];
    }

    public static function findByUsuarioId(int $usuarioId): array
    {
        $tasks = self::getAll();
        return array_filter($tasks, fn($task) => $task->usuario_id == $usuarioId);
    }
}
