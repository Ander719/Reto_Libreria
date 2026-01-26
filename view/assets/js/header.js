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
        // --- MODO USUARIO LOGUEADO ---
        welcomeText.textContent = `${currentUser.user_name}`;

        // Ocultamos "Iniciar Sesión"
        navItems[0].hidden = true;

        // Mostramos el resto de opciones (quitamos el atributo hidden)
        navItems.forEach((item, index) => {
            if (index === 1) item.hidden = false;
            if ((filter === "main" || filter === "admin") && index === 3) item.hidden = true;
            if (filter === "configProfile" && index === 1) item.hidden = true;
            if (filter === "main" && index === 4) item.hidden = true;
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
export function initSearchLogic() {
    const searchInput = document.getElementById('search-input');
    const clearBtn = document.getElementById('clearBtn');
    const suggestionsList = document.getElementById('suggestionsList');

    // A. Evento al escribir (KEYUP)
    searchInput.addEventListener('input', (e) => {
        const query = searchInput.value.trim();

        if (query.length > 0) {
            updateSuggestions(query);
        } else {
            suggestionsList.classList.remove('active'); // Ocultar si está vacío
            toggleSearchView(false); // Volver a home si borras todo
        }

    });
    suggestionsList.addEventListener('click', (e) => {
        // Buscamos el elemento .suggestion-item más cercano al click
        const item = e.target.closest('.suggestion-item');
        if (item) {
            const title = item.getAttribute('data-title'); // Cogemos el título guardado

            searchInput.value = title; // Ponemos el título en el input
            suggestionsList.classList.remove('active'); // Ocultamos lista

            performSearch(title); // Ejecutamos búsqueda oficial
        }
    });
    document.addEventListener('click', (e) => {
        const clickedInput = searchInput.contains(e.target);
        const clickedSuggestions = suggestionsList.contains(e.target);

        if (!clickedInput && !clickedSuggestions) {
            suggestionsList.classList.remove('active');
        } else if (clickedInput) {
            if (searchInput.value.trim().length > 0) {
                updateSuggestions(searchInput.value.trim());
            }
        }
    });
    // EXTRA: Si el usuario hace TAB hasta el input, también mostrar
    searchInput.addEventListener('focus', () => {
        if (searchInput.value.trim().length > 0) {
            updateSuggestions(searchInput.value.trim());
        }
    });
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            suggestionsList.classList.remove('active');
            performSearch(searchInput.value);
        }
    });

    // D. Evento botón X (Limpiar)
    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        toggleSearchView(false); // Volver al inicio
        searchInput.focus(); // Mantener foco
    });
}