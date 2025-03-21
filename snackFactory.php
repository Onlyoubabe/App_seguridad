<?php
require_once 'db.php';

class SnackFactory {

    

    // Crear un nuevo snack
    public static function crearSnack($nombre, $descripcion, $precio, $imagen) {
        $pdo = Database::getInstance();

        // Manejo de la imagen
        if (isset($imagen) && $imagen['error'] == 0) {
            $directorio = "uploads/";
            $nombreArchivo = time() . "_" . basename($imagen['name']);
            $rutaArchivo = $directorio . $nombreArchivo;

            if (move_uploaded_file($imagen['tmp_name'], $rutaArchivo)) {
                // Guardar en la base de datos
                $stmt = $pdo->prepare("INSERT INTO snacks (nombre, descripcion, precio, imagen) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nombre, $descripcion, $precio, $rutaArchivo]);

                return true; // Éxito
            } else {
                return false; // Error al mover la imagen 
            }
        } else {
            return false; // Imagen no válida
        }
    }

    // Obtener todos los snacks desde la base de datos
    public static function obtenerSnacks() {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("SELECT * FROM snacks");
        $snacks = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $snacks[] = $row;  // Almacena el resultado en un arreglo
        }
        return $snacks;
    }

    // Obtener un snack por ID
    public static function obtenerSnackPorId($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM snacks WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);  // Retorna el snack como un arreglo asociativo
    }

// Actualizar un snack
public static function actualizarSnack($id, $nombre, $descripcion, $precio, $imagen = null) {
    $pdo = Database::getInstance();

    // Armar la consulta de actualización
    $updateFields = [];
    $params = [];

    // Verificar si el nombre ha sido cambiado
    if ($nombre) {
        $updateFields[] = "nombre = ?";
        $params[] = $nombre;
    }

    // Verificar si la descripción ha sido cambiada
    if ($descripcion) {
        $updateFields[] = "descripcion = ?";
        $params[] = $descripcion;
    }

    // Verificar si el precio ha sido cambiado
    if ($precio) {
        $updateFields[] = "precio = ?";
        $params[] = $precio;
    }

    // Verificar si se ha proporcionado una imagen nueva
    if ($imagen && $imagen['error'] == 0) {
        $directorio = "uploads/";
        $nombreArchivo = time() . "_" . basename($imagen['name']);
        $rutaArchivo = $directorio . $nombreArchivo;

        if (move_uploaded_file($imagen['tmp_name'], $rutaArchivo)) {
            $updateFields[] = "imagen = ?";
            $params[] = $rutaArchivo;
        }
    }

    // Si no se han hecho cambios, no ejecutar la actualización
    if (empty($updateFields)) {
        return false;
    }

    // Agregar el parámetro de ID al final
    $params[] = $id;

    // Construir y ejecutar la consulta de actualización
    $updateQuery = "UPDATE snacks SET " . implode(", ", $updateFields) . " WHERE id = ?";
    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute($params);

    return true;
}


    // Eliminar un snack
    public static function eliminarSnack($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM snacks WHERE id = ?");
        $stmt->execute([$id]);
    }
}
?>
