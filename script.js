// Espera a que el contenido de la página se haya cargado completamente
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar'); // Selecciona el navbar

    // Función que se ejecuta al hacer scroll
    function handleScroll() {
        if (window.scrollY > 50) { // Cambia el valor según tus necesidades
            navbar.classList.add('scrolled'); // Añade la clase 'scrolled' si se ha hecho scroll
        } else {
            navbar.classList.remove('scrolled'); // Elimina la clase si no se ha hecho scroll
        }
    }

    // Escucha el evento de scroll
    window.addEventListener('scroll', handleScroll);
});

