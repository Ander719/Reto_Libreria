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
// --- FUNCIÓN PARA GENERAR SUGERENCIAS ---
function updateSuggestions(term) {
    const suggestionsList = document.getElementById('suggestionsList');
    term = term.toLowerCase();

    // 1. Filtrar libros (Igual que en performSearch)
    const matches = globalBooks.filter(book => {
        const title = book.title.toLowerCase();
        const isbn = book.isbn.toLowerCase();
        const authorName = book.author ? (book.author.name + " " + book.author.lastname).toLowerCase() : "";

        // Coincide con cualquiera de los 3
        return title.includes(term) || isbn.includes(term) || authorName.includes(term);
    });

    // 2. Extraer SOLO TÍTULOS ÚNICOS
    // Usamos Set para eliminar duplicados si hubiera libros con mismo nombre
    const uniqueTitles = [...new Set(matches.map(book => book.title))];

    // 3. Limitar a 5 o 6 sugerencias (para no llenar la pantalla)
    const topSuggestions = uniqueTitles.slice(0, 6);

    // 4. Renderizar HTML
    if (topSuggestions.length > 0) {
        suggestionsList.innerHTML = topSuggestions.map(title => `
            <div class="suggestion-item" data-title="${title}">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>${highlightMatch(title, term)}</span>
            </div>
        `).join('');

        suggestionsList.classList.add('active'); // Mostrar lista
    } else {
        suggestionsList.classList.remove('active'); // Ocultar si no hay nada
    }
}

// (Opcional) Función para poner en negrita la parte que coincide
function highlightMatch(text, term) {
    const regex = new RegExp(`(${term})`, 'gi');
    return text.replace(regex, '<b>$1</b>');
}
function performSearch(term) {
    if (!term) return;
    term = term.toLowerCase();

    console.log("Buscando:", term);

    // 1. Filtramos el array global
    const results = globalBooks.filter(book => {
        // Datos a buscar
        const title = book.title.toLowerCase();
        const isbn = book.isbn.toLowerCase();
        // Cuidado con author, verifica si es objeto o string en tu JSON final
        // Si tu JSON devuelve author: {name: '...', lastname: '...'}
        const authorName = (book.author.name + " " + book.author.lastname).toLowerCase();

        return title.includes(term) || isbn.includes(term) || authorName.includes(term);
    });

    // 2. RENDERIZADO EXCLUSIVO PARA BÚSQUEDA (Aquí estaba el fallo)
    // No llamamos a renderizarLibros(), lo hacemos manualmente en el contenedor de búsqueda
    const container = document.getElementById('searchResultsContainer'); // Asegúrate de tener este ID en tu HTML (div oculto searchSection)
    const template = document.getElementById('book-card-template');

    // Limpiamos resultados anteriores
    container.innerHTML = '';

    // Reutilizamos tu función 'renderCard' que funciona bien
    results.forEach(libro => {
        renderCard(libro, container, template);
    });

    // 3. Manejo de "Sin resultados"
    const noResults = document.getElementById('noResultsMessage');
    if (results.length === 0) {
        noResults.style.display = 'block';
    } else {
        noResults.style.display = 'none';
    }

    // 4. Cambiamos la vista
    toggleSearchView(true);
}

function toggleSearchView(isSearching) {
    const searchSection = document.getElementById('searchSection');
    const defaultSections = document.getElementById('defaultContent');

    if (isSearching) {
        searchSection.style.display = 'block';
        defaultSections.style.display = 'none';
    } else {
        searchSection.style.display = 'none';
        defaultSections.style.display = 'block';
    }
}