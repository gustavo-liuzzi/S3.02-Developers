<?php

enum TaskStatus: string {
    case PENDIENTE = 'pendiente';
    case EN_EJECUCION = 'en ejecucion';
    case COMPLETADA = 'completada';
}

class Task {
    public $id_tarea;
    public $titulo;
    public $descripcion;
    public TaskStatus $estado;
    public $fecha_inicio; //DateTime probar
    public $fecha_fin; //DateTime probar
    public $usuario_id;

    //Coonstante con la ruta al fichero JSON donde se guardan todas las tareas.
    const FILE_PATH = __DIR__ . '/../../data/tasks.json';

    public function __construct($data = []) {
        $this->id_tarea = $data["id_tarea"] ?? null;
        $this->titulo = $data["titulo"] ?? "";
        $this->descripcion = $data["descripcion"] ?? "";
        // Convertimos el string del JSON a un objeto TaskStatus (enum)
        $this->estado = isset($data["estado"]) ? TaskStatus::from ($data["estado"]) : TaskStatus::PENDIENTE;
        $this->fecha_inicio = $data["fecha_inicio"] ?? "";
        $this->fecha_fin = $data["fecha_fin"] ?? "";
        $this->usuario_id = $data["usuario_id"] ?? null;
    }

    //Funcion para listar todas las tareas
    public static function getAll() {
        if (!file_exists(self::FILE_PATH)) {
            return [];
        }
        
        $json = file_get_contents(self::FILE_PATH);
        $tasksArr = json_decode($json, true) ?: [];
        // Convertir cada registro a objeto Task
        return array_map(fn($t) => new Task($t), $tasksArr);
    }

    //Funcion para buscar tarea por ID
    public static function findById($id_tarea) {
        $tasks = self::getAll();
        foreach ($tasks as $task) {
            if ($task->id_tarea == $id_tarea) {
                return $task;
            }
        }
        return null;
    }

    //Funcion para guardar una nueva tarea
    public function save() {
        $tasks = self::getAll();
        if (!$this->id_tarea) {
            $this->id_tarea = count($tasks) ? max(array_map(fn($t) => $t->id_tarea, $tasks)) + 1 : 1;
        }
        $tasks[] = $this;
        self::saveAll($tasks);
        return true;
    }

    //Funcion para actualizar tarea existente
    public function update() {
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

    //Funcion para borrar tarea por ID
    public static function delete ($id_tarea) {
        $tasks = self::getAll();
        $tasks = array_filter($tasks, fn($task) => $task->id_tarea != $id_tarea);
        self::saveAll($tasks);
        return true;
    }

    //Funcion para guardar todas las tareas al ficher JSON (funcion privada)
    private static function saveAll($tasks) {
        $arr = array_map(fn($t) => $t->toArray(), $tasks);
        file_put_contents(self::FILE_PATH, json_encode($arr, JSON_PRETTY_PRINT));
    }

    //Funcion para convertir objeto en array (para guardar en JSON)
    public function toArray() {
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
}

?>