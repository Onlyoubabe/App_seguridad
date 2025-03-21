<?php
require_once 'db.php';
require_once 'PizzaFactory.php';
require_once 'SnackFactory.php'; // Incluir el SnackFactory
session_start();

// Obtener la conexión a la base de datos
$pdo = Database::getInstance();

// Obtener todas las pizzas
$stmt = $pdo->query("SELECT * FROM pizzas");
$pizzas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los snacks usando SnackFactory
$snacks = SnackFactory::obtenerSnacks();
?>

<?php include('navbar.php'); ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Pizzería</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Bienvenido a la Pizzería</h2>

        <center><h3>Nuestras Pizzas</h3></center>
        <div class="pizzas-grid">
            <?php foreach ($pizzas as $pizza): ?>
                <div class="pizza-card">
                    <img src="<?php echo htmlspecialchars($pizza['imagen']); ?>" alt="Imagen de <?php echo htmlspecialchars($pizza['nombre']); ?>" class="pizza-img">
                    <h4><?php echo htmlspecialchars($pizza['nombre']); ?></h4>
                    <p><?php echo htmlspecialchars($pizza['descripcion']); ?></p>
                    <p><strong>Precio:</strong> $<?php echo number_format($pizza['precio'], 2); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <center><h3>Nuestros Snacks</h3></center>
        <div class="snacks-grid">
            <?php foreach ($snacks as $snack): ?>
                <div class="snack-card">
                    <img src="<?php echo htmlspecialchars($snack['imagen']); ?>" alt="Imagen de <?php echo htmlspecialchars($snack['nombre']); ?>" class="snack-img">
                    <h4><?php echo htmlspecialchars($snack['nombre']); ?></h4>
                    <p><?php echo htmlspecialchars($snack['descripcion']); ?></p>
                    <p><strong>Precio:</strong> $<?php echo number_format($snack['precio'], 2); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="script.js"></script> 
</body>
</html>
