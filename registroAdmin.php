<?php
// Incluir la clase de conexión y verificar el rol del usuario
require_once 'db.php';
session_start();

// Verificar si el usuario está logueado y es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Si no es admin, redirigir al inicio
    header('Location: index.php');
    exit();
}

// Generar un token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Crear un token CSRF seguro
}

// Verificar si se ha enviado el formulario de registro
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar el token CSRF
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {

        // Obtener los datos del formulario y sanitizar la entrada
        $nombre = htmlspecialchars(trim($_POST['nombre']));
        $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL); // Sanitiza el correo
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validar que el correo sea válido
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $error = "Correo electrónico no válido.";
        }
        // Validar que las contraseñas coincidan
        elseif ($password !== $confirm_password) {
            $error = "Las contraseñas no coinciden.";
        } else {
            // Encriptar la contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Conectar a la base de datos
            $pdo = Database::getInstance();

            // Comprobar si el correo ya existe
            $stmt = $pdo->prepare("SELECT * FROM users WHERE correo = :correo");
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user) {
                // Si el usuario ya existe, mostrar un mensaje de error
                $error = "El correo electrónico ya está registrado.";
            } else {
                // Insertar el nuevo usuario con rol 'admin' en la base de datos
                $stmt = $pdo->prepare("INSERT INTO users (nombre, correo, contrasena, role) VALUES (:nombre, :correo, :contrasena, :role)");
                $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
                $stmt->bindParam(':contrasena', $hashed_password, PDO::PARAM_STR);
                $stmt->bindParam(':role', $role = 'admin', PDO::PARAM_STR);
                $stmt->execute();

                // Redirigir al administrador a la página de gestión de usuarios
                header("Location: vistaAdmin.php");
                exit();
            }
        }
    } else {
        // Si el token CSRF no coincide
        $error = "Error en la verificación del formulario.";
    }
}
?>

<?php include('navbar.php'); ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Administrador</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Registrar Administrador</h2>

        <!-- Mostrar el mensaje de error si existe -->
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <!-- Formulario para registrar un nuevo administrador -->
        <form action="registroAdmin.php" method="POST">
            <!-- Incluir el token CSRF en el formulario -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="email" name="correo" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <input type="password" name="confirm_password" placeholder="Confirmar Contraseña" required>
            <button type="submit" name="submit">Registrar Administrador</button>
        </form>
    </div>
</body>
</html>
