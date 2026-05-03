import { checkSession, currentUser } from './session.js';
import { loadHeader,loadFooter } from './header.js';

init();

let globalBooks = [];

async function init() {
    await checkSession();

    await loadHeader("main");
    await loadFooter();
    await cargarLibrosDesdeBD();

    initSearchLogic();
}
function initSearchLogic() {
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
    // No llamamos a renderBooks(), lo hacemos manualmente en el contenedor de búsqueda
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

/**
 * Función que genera el HTML de las estrellas
 */
function getEstrellasHTML(rating) {
    let html = '';
    for (let i = 1; i <= 5; i++) {
        if (rating >= i) {
            html += '<i class="fa-solid fa-star"></i>'; // Llena
        } else if (rating >= i - 0.5) {
            html += '<i class="fa-solid fa-star-half-stroke"></i>'; // Media
        } else {
            html += '<i class="fa-regular fa-star"></i>'; // Vacía
        }
    }
    return html;
}

// --- NUEVA FUNCIÓN PARA PEDIR LIBROS ---
async function cargarLibrosDesdeBD() {

    try {
        const response = await fetch('../../api/GetAllBooks.php');
        console.log("Status GetAllBooks:", response.status);
        const rawText = await response.text();

        let data;
        try {
            data = JSON.parse(rawText);
        } catch (error) {
            console.error("❌ El servidor no devolvió JSON. Devolvió esto:\n", rawText);
            return;
        }

        //console.log("Datos recibidos:", data);

        if (data.status === "success") {
            const books = data.data || [];
            globalBooks = books; // Guardamos los libros globalmente si es necesario
            renderBooks(books);
        } else {
            console.error("Error al cargar libros");
        }
    } catch (error) {
        console.error("Error:", error);
    }
}
/**
 * Función que clona el template y pinta los libros
 */
function renderBooks(listaLibros) {
    rngBooksRender(listaLibros);
    ratingBooksRender(listaLibros);
}
// Función para renderizar los libros aleatorios
function rngBooksRender(listaLibros) {
    const contenedor = document.getElementById('rngBooksContainer');
    const template = document.getElementById('book-card-template');

    // Limpiamos el contenedor por si acaso
    contenedor.innerHTML = '';

    //mezclamos los libros aleatoriamente
    listaLibros.sort(() => Math.random() - 0.5);

    listaLibros.forEach(libro => {
        renderCard(libro, contenedor, template);
    });
}

// Función para renderizar los libros mejor valorados
function ratingBooksRender(listaLibros) {
    const contenedor = document.getElementById('ratingBooksContainer');
    const template = document.getElementById('book-card-template');
    // Limpiamos el contenedor por si acaso
    contenedor.innerHTML = '';
    //ordenamos por rating (los mejor valorados primero)
    listaLibros.sort((a, b) => b.rating - a.rating);
    //tomamos solo los 4 primeros
    const mejorValorados = listaLibros.slice(0, 4);
    mejorValorados.forEach(libro => {
        renderCard(libro, contenedor, template);
    });
}
function renderCard(libro, contenedor, template) {
    // Clonamos el contenido del template
    const clone = template.content.cloneNode(true);

    // Rellenamos los datos buscando por clase dentro del clon
    clone.querySelector('.book-title').textContent = libro.title;
    const author = libro.author.name + " " + libro.author.lastname;
    clone.querySelector('.book-author').textContent = author.trim() === "" ? "Autor Desconocido" : author;
    clone.querySelector('.book-price').textContent = `${libro.price}€`;
    clone.querySelector('.book-stock').textContent = `Stock: ${libro.stock}`;

    // Imagen (si no tienes imagen real, usa un placeholder)
    const imgElement = clone.querySelector('.book-image-card');
    imgElement.src = "../assets/img/covers/" + (libro.cover || "mood-heart.png");
    imgElement.alt = `Portada de ${libro.title}`;

    // Inyectamos las estrellas
    const ratingContainer = clone.querySelector('.book-rating');
    ratingContainer.innerHTML = getEstrellasHTML(libro.rating);

    // Añadimos el evento click a la tarjeta
    const bookcard = clone.querySelector('.book-card');
    bookcard.onclick = () => {
        // Redirigimos pasando el ISBN en la URL
        window.location.href = `bookDetails.html?isbn=${libro.isbn}`;
    };
    // Añadimos la tarjeta al grid
    contenedor.appendChild(clone);
}
