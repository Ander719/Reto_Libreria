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
        if (event.target && event.target.textContent === 'volver') {
            event.preventDefault();
            window.location.href = document.referrer;
        }
    });

    // Seleccionamos los elementos del DOM
    const welcomeText = document.querySelector('.welcome-text');
    const navItems = document.querySelectorAll('.nav-menu li');
    const searchGroup = document.querySelector('.search-group')
    if (filter !== "main") {
        searchGroup.style.display = "none";
    }
    //console.log(searchGroup);
    // navItems[0] es "Iniciar Sesión"
    // navItems[1] es "Opciones", navItems[2] es "Cerrar Sesión", etc.
    if (currentUser) {
        if (currentUser.role === "admin") navItems[3].hidden = true;
        // --- MODO USUARIO LOGUEADO ---
        welcomeText.textContent = `${currentUser.user_name}`;

        // Ocultamos "Iniciar Sesión"
        navItems[0].hidden = true;

        // Mostramos el resto de opciones (quitamos el atributo hidden)
        navItems.forEach((item, index) => {
            if (index === 1) item.hidden = false;
            if ((filter === "main" || filter === "opcAdmin" || filter === "deleteComment" || filter === "configProfile") && index === 3) item.hidden = true;
            if (filter === "configProfile" && index === 1) item.hidden = true;
            if (filter === "main" && index === 4) item.hidden = true;
        });

    } else {
        // --- MODO VISITANTE ---
        welcomeText.textContent = "Bienvenido";

        // Por defecto solo mostramos "Iniciar Sesión"
        showOnly([0]);

        if (filter === "logInSignUp") {
            // Mostramos solo el item 4
            showOnly([4]);
        }
    }
}
function showOnly(indices = []) {
    const navItems = document.querySelectorAll('.nav-menu li');
    navItems.forEach((item, index) => {
        item.hidden = !indices.includes(index);
    });
}
export async function loadFooter(){
    const footerLinks = document.querySelectorAll("#footerLink li")
    console.log(footerLinks)
    footerLinks[1].addEventListener("click",()=>{
        window.location.href="main.html";
    });
    footerLinks[2].addEventListener("click",()=>{
        window.location.href="https://www.osakidetza.euskadi.eus/portada/";
    });
    footerLinks[3].addEventListener("click",()=>{
        window.location.href="https://www.tartanga.eus/";
    });
}