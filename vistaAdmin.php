<?php
// Incluir la clase de conexión y la Factory
require_once 'db.php';
require_once 'PizzaFactory.php';
session_start();

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php'); // Si no está logueado o no es admin, redirigir al inicio
    exit();
}

// Generar un token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Crear un token CSRF seguro
}

// Obtener las pizzas desde la base de datos
$pizzas = PizzaFactory::obtenerPizzas();

// Función para eliminar una pizza
if (isset($_GET['delete'])) {
    $pizza_id = (int) $_GET['delete'];  // Sanitizar el ID
    PizzaFactory::eliminarPizza($pizza_id);
    header('Location: vistaAdmin.php');
    exit();
}

// Crear una nueva pizza
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_pizza'])) {
    // Validación y sanitización de las entradas
    $nombre = htmlspecialchars(trim($_POST['nombre']));
    $descripcion = htmlspecialchars(trim($_POST['descripcion']));
    $precio = filter_var($_POST['precio'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $imagen = $_FILES['imagen'];

    // Validación de precio
    if (!is_numeric($precio) || $precio <= 0) {
        $error = "El precio debe ser un número válido.";
    } else {
        $success = PizzaFactory::agregarPizza($nombre, $descripcion, $precio, $imagen);

        if ($success) {
            header('Location: vistaAdmin.php');
            exit();
        } else {
            $error = "Hubo un error al agregar la pizza.";
        }
    }
}

// Actualizar una pizza
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_pizza'])) {
    // Validación y sanitización de las entradas
    $pizza_id = (int) $_POST['pizza_id'];
    $nombre = htmlspecialchars(trim($_POST['nombre']));
    $descripcion = htmlspecialchars(trim($_POST['descripcion']));
    $precio = filter_var($_POST['precio'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $imagen = $_FILES['imagen'];

    // Validación de precio
    if (!is_numeric($precio) || $precio <= 0) {
        $error = "El precio debe ser un número válido.";
    } else {
        $success = PizzaFactory::actualizarPizza($pizza_id, $nombre, $descripcion, $precio, $imagen);

        if ($success) {
            header('Location: vistaAdmin.php');
            exit();
        } else {
            $error = "Hubo un error al actualizar la pizza.";
        }
    }
}

// Obtener los datos de la pizza a editar
$edit_pizza = null;
if (isset($_GET['edit'])) {
    $pizza_id = (int) $_GET['edit'];  // Sanitizar el ID
    $edit_pizza = PizzaFactory::obtenerPizzaPorId($pizza_id);
}
?>

<?php include('navbar.php'); ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestión de Pizzas</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Gestión de Pizzas</h2>

        <!-- Mostrar el mensaje de error si existe -->
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <!-- Formulario para agregar o actualizar una pizza -->
        <h3><?php echo $edit_pizza ? "Editar Pizza" : "Agregar Pizza"; ?></h3>
        <form action="vistaAdmin.php" method="POST" enctype="multipart/form-data">
            <!-- Incluir el token CSRF en el formulario -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <input type="hidden" name="pizza_id" value="<?php echo htmlspecialchars($edit_pizza['id'] ?? ''); ?>">
            <input type="text" name="nombre" placeholder="Nombre de la pizza" value="<?php echo htmlspecialchars($edit_pizza['nombre'] ?? ''); ?>" required>
            <textarea name="descripcion" placeholder="Descripción de la pizza" required><?php echo htmlspecialchars($edit_pizza['descripcion'] ?? ''); ?></textarea>
            <input type="number" step="0.01" name="precio" placeholder="Precio de la pizza" value="<?php echo htmlspecialchars($edit_pizza['precio'] ?? ''); ?>" required>
            <input type="file" name="imagen" accept="image/*">
            <button type="submit" name="<?php echo $edit_pizza ? "update_pizza" : "add_pizza"; ?>">
                <?php echo $edit_pizza ? "Actualizar Pizza" : "Agregar Pizza"; ?>
            </button>
        </form>

        <h3>Pizzas Disponibles</h3>
        <!-- Listar todas las pizzas -->
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
                <?php foreach ($pizzas as $pizza): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pizza['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($pizza['descripcion']); ?></td>
                        <td>$<?php echo number_format($pizza['precio'], 2); ?></td>
                        <td>
                            <?php if ($pizza['imagen']): ?>
                                <img src="<?php echo htmlspecialchars($pizza['imagen']); ?>" alt="Imagen de <?php echo htmlspecialchars($pizza['nombre']); ?>" width="100">
                            <?php else: ?>
                                <p>No image</p>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="vistaAdmin.php?edit=<?php echo $pizza['id']; ?>">
                                <button class="edit-btn">Editar</button>
                            </a> |
                            <a href="vistaAdmin.php?delete=<?php echo $pizza['id']; ?>" onclick="return confirm('¿Estás seguro de eliminar esta pizza?');">
                                <button class="delete-btn">Eliminar</button>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
