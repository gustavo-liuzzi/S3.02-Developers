<?php
declare(strict_types=1);

class JsonHandler {
    private string $filepath;

    public function __construct(string $filepath)
    {
        $this->filepath = $filepath;
    }

    public function leer(): array {
        if (!file_exists(($this->filepath))) {
            return [];
        }
        $content = file_get_contents($this->filepath);
        return json_decode($content, true) ?? [];
    }

    public function guardar(array $data): void
    {
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT);
        if ($jsonContent === false) {
            throw new Exception("Error al convertir los datos a JSON");
        }
        file_put_contents($this->filepath, $jsonContent);
    }
}