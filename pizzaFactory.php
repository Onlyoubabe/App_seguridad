<?php
require_once 'db.php';

class PizzaFactory {
    public static function crearPizza($id, $nombre, $descripcion, $precio, $imagen = null) {
        return [
            'id' => $id,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'imagen' => $imagen
        ];
    }

    public static function agregarPizza($nombre, $descripcion, $precio, $imagen) {
        $pdo = Database::getInstance();

        // Manejo de la imagen
        if (isset($imagen) && $imagen['error'] == 0) {
            $directorio = "uploads/";
            $nombreArchivo = time() . "_" . basename($imagen['name']);
            $rutaArchivo = $directorio . $nombreArchivo;

            if (move_uploaded_file($imagen['tmp_name'], $rutaArchivo)) {
                // Insertar en la base de datos
                $stmt = $pdo->prepare("INSERT INTO pizzas (nombre, descripcion, precio, imagen) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nombre, $descripcion, $precio, $rutaArchivo]);

                return true; // Éxito
            } else {
                return false; // Error al mover la imagen
            }
        } else {
            // Si no hay imagen, no la agregamos
            $stmt = $pdo->prepare("INSERT INTO pizzas (nombre, descripcion, precio) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $descripcion, $precio]);

            return true; // Éxito
        }

        return false; // Fallo en el proceso
    }

    public static function obtenerPizzas() {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("SELECT * FROM pizzas");
        $pizzas = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pizzas[] = self::crearPizza($row['id'], $row['nombre'], $row['descripcion'], $row['precio'], $row['imagen']);
        }

        return $pizzas;
    }

    public static function obtenerPizzaPorId($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM pizzas WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return self::crearPizza($row['id'], $row['nombre'], $row['descripcion'], $row['precio'], $row['imagen']);
    }

    public static function actualizarPizza($id, $nombre, $descripcion, $precio, $imagen = null) {
        $pdo = Database::getInstance();

        if (isset($imagen) && $imagen['error'] == 0) {
            $directorio = "uploads/";
            $nombreArchivo = time() . "_" . basename($imagen['name']);
            $rutaArchivo = $directorio . $nombreArchivo;

            if (move_uploaded_file($imagen['tmp_name'], $rutaArchivo)) {
                // Actualizar en la base de datos
                $stmt = $pdo->prepare("UPDATE pizzas SET nombre = ?, descripcion = ?, precio = ?, imagen = ? WHERE id = ?");
                $stmt->execute([$nombre, $descripcion, $precio, $rutaArchivo, $id]);

                return true; // Éxito
            } else {
                return false; // Error al mover la imagen
            }
        } else {
            // Si no hay imagen, actualizar sin imagen
            $stmt = $pdo->prepare("UPDATE pizzas SET nombre = ?, descripcion = ?, precio = ? WHERE id = ?");
            $stmt->execute([$nombre, $descripcion, $precio, $id]);

            return true; // Éxito
        }

        return false; // Fallo en el proceso
    }

    public static function eliminarPizza($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM pizzas WHERE id = ?");
        $stmt->execute([$id]);
    }
}

?>
