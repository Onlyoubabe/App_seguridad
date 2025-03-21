<?php
// Incluir la clase de conexión
require 'db.php';

// Iniciar la sesión para gestionar el registro
session_start();

// Generar un token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Crear un token CSRF seguro
}

// Verificar si el usuario ya está registrado o si hay datos en el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar el token CSRF
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {

        // Obtener los datos del formulario y limpiarlos
        $nombre = htmlspecialchars($_POST['nombre']);
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
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Obtener la conexión a la base de datos
            $pdo = Database::getInstance();

            // Verificar si el correo ya está registrado
            $stmt = $pdo->prepare("SELECT * FROM users WHERE correo = :correo");
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            $stmt->execute();
            $existingUser = $stmt->fetch();

            if ($existingUser) {
                $error = "El correo ya está registrado.";
            } else {
                // Insertar el nuevo usuario en la base de datos
                $stmt = $pdo->prepare("INSERT INTO users (nombre, correo, contrasena) VALUES (:nombre, :correo, :contrasena)");
                $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
                $stmt->bindParam(':contrasena', $hashedPassword, PDO::PARAM_STR);
                $stmt->execute();

                // Redirigir al usuario a la página de login después del registro
                $_SESSION['success'] = "Usuario registrado exitosamente!";
                header("Location: login.php");
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
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Registrar Usuario</h2>

        <!-- Mostrar mensajes de error o éxito -->
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p> <!-- Escapar salida -->
        <?php elseif (isset($_SESSION['success'])): ?>
            <p class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>

        <form action="registro.php" method="POST">
            <!-- Incluir el token CSRF en el formulario -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <input type="text" name="nombre" placeholder="Nombre de usuario" required>
            <input type="email" name="correo" placeholder="Correo electrónico" class="input-field" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <input type="password" name="confirm_password" placeholder="Confirmar Contraseña" required>
            <button type="submit">Registrar</button>
        </form>

        <p>¿Ya tienes una cuenta? <a href="login.php">Iniciar sesión</a></p>
    </div>
</body>
</html>
