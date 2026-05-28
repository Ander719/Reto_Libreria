import { currentUser, logout } from './session.js';

// Ajusta la cabecera segun el usuario y la pagina donde esta
export async function loadHeader(filter) {
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
            event.preventDefault();
            logout();
        }
        if (event.target && event.target.textContent === 'volver') {
            event.preventDefault();
            window.location.href = document.referrer;
        }
    });

    const welcomeText = document.querySelector('.welcome-text');
    const navItems = document.querySelectorAll('.nav-menu li');
    const searchGroup = document.querySelector('.search-group')
    if (filter !== "main") {
        searchGroup.style.display = "none";
    }
    // El menu depende del orden fijo de los <li> definidos en cada pagina HTML.
    if (currentUser) {
        if (currentUser.role === "admin") navItems[3].hidden = true;
        welcomeText.textContent = `${currentUser.user_name}`;

        navItems[0].hidden = true;

        navItems.forEach((item, index) => {
            if (index === 1) item.hidden = false;
            if ((filter === "main" || filter === "opcAdmin" || filter === "deleteComment") && index === 3) item.hidden = true;
            if (filter === "configProfile" && index === 1) item.hidden = true;
            if (filter === "main" && index === 4) item.hidden = true;
        });

    } else {
        welcomeText.textContent = "Bienvenido";

        showOnly([0]);

        if (filter === "logInSignUp") {
            showOnly([4]);
        }
    }
}

// Muestra solo algunos enlaces del menu de navegacion
function showOnly(indices = []) {
    const navItems = document.querySelectorAll('.nav-menu li');
    navItems.forEach((item, index) => {
        item.hidden = !indices.includes(index);
    });
}

// Enlaza las opciones del footer para que hagan cosas al hacer click
export async function loadFooter(){
    const footerLinks = document.querySelectorAll("#footerLink li")
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
