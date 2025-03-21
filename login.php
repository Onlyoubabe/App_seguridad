<?php
// Incluir la clase de conexión a la base de datos
require_once 'db.php';

session_start();

// Si ya está autenticado, redirigir a la vista correspondiente
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: vistaAdmin.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

// Generar un token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Crear un token CSRF seguro
}

// Verificar si se ha enviado el formulario de inicio de sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar el token CSRF
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
        
        // Obtener los datos del formulario y sanitizarlos
        $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL); // Sanitiza el correo
        $password = $_POST['password'];

        // Validar que el correo electrónico sea válido
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $error = "Correo electrónico no válido.";
        } else {
            // Obtener la conexión a la base de datos
            $pdo = Database::getInstance();

            // Preparar la consulta para obtener el usuario
            $stmt = $pdo->prepare("SELECT * FROM users WHERE correo = :correo");
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch();

            // Verificar si el usuario existe y la contraseña es correcta
            if ($user && password_verify($password, $user['contrasena'])) {
                // Iniciar la sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['correo'] = $user['correo'];
                $_SESSION['role'] = $user['role'];  // Asegúrate de que la columna 'role' exista en tu base de datos

                // Redirigir a vistaAdmin.php directamente (solo si el rol es admin)
                if ($user['role'] === 'admin') {
                    header('Location: vistaAdmin.php');
                } else {
                    header('Location: index.php');  // Redirigir a la página de inicio si no es admin
                }
                exit();
            } else {
                // Si las credenciales son incorrectas
                $error = "Correo o contraseña incorrectos.";
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
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Iniciar sesión</h2>
        
        <!-- Mostrar el mensaje de error si existe -->
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p> <!-- Escapar salida -->
        <?php endif; ?>

        <form action="login.php" method="POST">
            <!-- Incluir el token CSRF en el formulario -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <input type="email" name="correo" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>

        <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
    </div>
</body>
</html>
