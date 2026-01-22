import { currentUser, logout } from './session.js';

export async function loadHeader(filter) {
    console.log("Cargando header con filtro:", filter);
    console.log("Usuario actual en header:", currentUser);
    if (currentUser) {
        const opcionesLink = document.getElementById('opcionesLink');
        if (currentUser.role === 'admin') {
            opcionesLink.href = 'opcAdmin.html';
        } else {
            opcionesLink.href = 'configProfile.html';
        }
    }
    document.querySelector('.nav-menu').addEventListener('click', (event) => {
        if (event.target && event.target.id === 'btnLogout') {
            event.preventDefault(); // Prevenir el comportamiento por defecto del enlace
            logout(); // Llamar a la función de logout
        }
    });

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
            if (index === 1) item.hidden = false;
            if ((filter === "main" || filter === "admin") && index === 3) item.hidden = true;
            if (filter === "configProfile" && index === 1) item.hidden = true;
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
