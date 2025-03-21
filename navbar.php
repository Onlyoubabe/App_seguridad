<nav class="navbar">
    <!-- Logo o nombre del sitio -->
    <a href="index.php" class="logo">Pizzería</a>

    <!-- Enlaces del Navbar -->
    <ul class="nav-links">
        <li><a href="index.php">Inicio</a></li>
        <li><a href="sobre_Nosotros.php">Sobre Nosotros</a></li>

        <?php 
        // Verificar si el usuario está logueado
        if (isset($_SESSION['user_id'])):
            // Si el rol es 'admin', mostrar los enlaces de administración
            if ($_SESSION['role'] === 'admin'): ?>
                <li><a href="vistaAdmin.php">Registrar Pizzas</a></li>
                <li><a href="registroAdmin.php">Registrar Admin</a></li> <!-- Enlace para registrar admin -->
                <li><a href="vistaSnacks.php">Registrar Snacks</a></li> <!-- Enlace para gestionar snacks -->
            <?php endif; ?>

            <!-- Mostrar el enlace de Cerrar sesión -->
            <li><a href="logout.php">Cerrar Sesión</a></li>

        <?php else: ?>
            <!-- Si no está logueado, mostrar el enlace de Iniciar sesión -->
            <li><a href="login.php">Iniciar Sesión</a></li>
        <?php endif; ?>
    </ul>

    <!-- Ícono del menú para móviles -->
    <div class="menu-icon" onclick="toggleMenu()">☰</div>
</nav>

<!-- Script para mostrar/ocultar el menú en móviles -->
<script>
    function toggleMenu() {
        const navLinks = document.querySelector('.nav-links');
        navLinks.classList.toggle('active');
    }
</script>
