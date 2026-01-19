export function loadHeader(currentUser) {
    // Seleccionamos los elementos del DOM
    const welcomeText = document.querySelector('.welcome-text');
    const navItems = document.querySelectorAll('.nav-menu li');
    // navItems[0] es "Iniciar Sesión"
    // navItems[1] es "Opciones", navItems[2] es "Cerrar Sesión", etc.

    if (currentUser) {
        // --- MODO USUARIO LOGUEADO ---
        welcomeText.textContent = `${currentUser.user_name}`;

        // Ocultamos "Iniciar Sesión"
        navItems[0].hidden = true;

        // Mostramos el resto de opciones (quitamos el atributo hidden)
        navItems.forEach((item, index) => {
            if (index > 0) item.hidden = false;
            if (index === 4) {
                item.hidden = true; // Ocultamos "Volver" en el main
            }
            /*
            if (index === 4) {
                // "Volver" apunta a la página anterior
                item.querySelector('a').href = document.referrer || 'main.html';
            }
            */
        });

    } else {
        // --- MODO VISITANTE ---
        welcomeText.textContent = "Bienvenido";

        // Mostramos "Iniciar Sesión"
        navItems[0].hidden = false;

        // Ocultamos las opciones privadas
        navItems.forEach((item, index) => {
            if (index > 0) item.hidden = true;
        });
    }
}
