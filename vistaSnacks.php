<?php
require_once 'snackFactory.php';
session_start();

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Obtener todos los snacks
$snacks = SnackFactory::obtenerSnacks();

// Verificar si se realizó alguna acción de CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'Agregar') {
            // Agregar un nuevo snack
            $nombre = htmlspecialchars(trim($_POST['nombre'])); // Sanitización y validación
            $descripcion = htmlspecialchars(trim($_POST['descripcion']));
            $precio = floatval($_POST['precio']); // Asegurarse de que es un número válido
            $imagen = $_FILES['imagen'];

            if (!empty($nombre) && !empty($descripcion) && $precio > 0 && isset($imagen)) {
                SnackFactory::crearSnack($nombre, $descripcion, $precio, $imagen);
            }
        } elseif ($_POST['action'] == 'Actualizar') {
            // Actualizar un snack
            $id = intval($_POST['id']);
            $nombre = htmlspecialchars(trim($_POST['nombre']));
            $descripcion = htmlspecialchars(trim($_POST['descripcion']));
            $precio = floatval($_POST['precio']);
            $imagen = $_FILES['imagen'];

            if ($id > 0 && !empty($nombre) && !empty($descripcion) && $precio > 0) {
                SnackFactory::actualizarSnack($id, $nombre, $descripcion, $precio, $imagen);
            }
        } elseif ($_POST['action'] == 'Eliminar') {
            // Eliminar un snack
            $id = intval($_POST['id']);
            if ($id > 0) {
                SnackFactory::eliminarSnack($id);
            }
        }

        // Redirigir de nuevo al CRUD de snacks
        header("Location: vistaSnacks.php");
        exit();
    }
}

// Obtener el snack a editar
$edit_snack = null;
if (isset($_GET['edit'])) {
    $snack_id = intval($_GET['edit']);
    $edit_snack = SnackFactory::obtenerSnackPorId($snack_id);
}
?>

<?php include('navbar.php'); ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Snacks</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Gestión de Snacks</h2>

        <!-- Formulario para agregar o actualizar un snack -->
        <h3><?php echo $edit_snack ? "Editar Snack" : "Agregar Snack"; ?></h3>
        <form action="vistaSnacks.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $edit_snack['id'] ?? ''; ?>">
            <input type="text" name="nombre" placeholder="Nombre del snack" value="<?php echo htmlspecialchars($edit_snack['nombre'] ?? ''); ?>" required>
            <textarea name="descripcion" placeholder="Descripción del snack" required><?php echo htmlspecialchars($edit_snack['descripcion'] ?? ''); ?></textarea>
            <input type="number" step="0.01" name="precio" placeholder="Precio del snack" value="<?php echo htmlspecialchars($edit_snack['precio'] ?? ''); ?>" required>
            <input type="file" name="imagen" accept="image/*">
            <button type="submit" name="action" value="<?php echo $edit_snack ? 'Actualizar' : 'Agregar'; ?>">
                <?php echo $edit_snack ? "Actualizar Snack" : "Agregar Snack"; ?>
            </button>
        </form>

        <h3>Snacks Disponibles</h3>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Imagen</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($snacks as $snack): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($snack['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($snack['descripcion']); ?></td>
                        <td>$<?php echo number_format($snack['precio'], 2); ?></td>
                        <td>
                            <?php if ($snack['imagen']): ?>
                                <img src="<?php echo htmlspecialchars($snack['imagen']); ?>" alt="Imagen de <?php echo htmlspecialchars($snack['nombre']); ?>" width="100">
                            <?php else: ?>
                                <p>No image</p>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="vistaSnacks.php?edit=<?php echo $snack['id']; ?>">
                                <button class="edit-btn">Editar</button>
                            </a>
                            <form action="vistaSnacks.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar este snack?');">
                            <input type="hidden" name="id" value="<?php echo $snack['id']; ?>">
                            <button type="submit" name="action" value="Eliminar" class="delete-btn">Eliminar</button>
                            </form>
                         </td>
                    </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
