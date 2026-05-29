import { checkSession, currentUser } from './session.js';
import { loadHeader,loadFooter } from './header.js';
import { apiFetch } from './apiClient.js';

init();

// Guarda los libros que se cargan desde la base de datos
let globalBooks = [];

// Inicia la pagina cargando sesion, cabecera, footer y libros
async function init() {
    await checkSession();

    await loadHeader("main");
    await loadFooter();
    await cargarLibrosDesdeBD();

    initSearchLogic();
}

// Prepara el buscador con sugerencias y resultados
function initSearchLogic() {
    const searchInput = document.getElementById('search-input');
    const clearBtn = document.getElementById('clearBtn');
    const suggestionsList = document.getElementById('suggestionsList');

    // Si el campo queda vacio, volvemos a las secciones normales de portada.
    searchInput.addEventListener('input', (e) => {
        const query = searchInput.value.trim();

        if (query.length > 0) {
            updateSuggestions(query);
        } else {
            suggestionsList.classList.remove('active');
            toggleSearchView(false);
        }

    });
    // La lista se genera al vuelo, por eso se escucha el click en el contenedor.
    suggestionsList.addEventListener('click', (e) => {
        const item = e.target.closest('.suggestion-item');
        if (item) {
            const title = item.getAttribute('data-title');

            searchInput.value = title;
            suggestionsList.classList.remove('active');

            performSearch(title);
        }
    });
    // Click fuera: cerrar. Focus con texto: volver a mostrar.
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

    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        toggleSearchView(false);
        searchInput.focus();
    });
}

// Busca sugerencias por titulo, ISBN o autor
function updateSuggestions(term) {
    const suggestionsList = document.getElementById('suggestionsList');
    term = term.toLowerCase();

    const matches = globalBooks.filter(book => {
        const title = book.title.toLowerCase();
        const isbn = book.isbn.toLowerCase();
        const authorName = book.author ? (book.author.name + " " + book.author.lastname).toLowerCase() : "";

        return title.includes(term) || isbn.includes(term) || authorName.includes(term);
    });

    // Puede haber coincidencias repetidas; Set deja solo un titulo de cada.
    const uniqueTitles = [...new Set(matches.map(book => book.title))];

    const topSuggestions = uniqueTitles.slice(0, 6);

    if (topSuggestions.length > 0) {
        suggestionsList.innerHTML = topSuggestions.map(title => `
            <div class="suggestion-item" data-title="${title}">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>${highlightMatch(title, term)}</span>
            </div>
        `).join('');

        suggestionsList.classList.add('active');
    } else {
        suggestionsList.classList.remove('active');
    }
}

// Resalta el texto que coincide con lo que busca el usuario
function highlightMatch(text, term) {
    const regex = new RegExp(`(${term})`, 'gi');
    return text.replace(regex, '<b>$1</b>');
}

// Muestra los resultados de la busqueda en la pantalla
function performSearch(term) {
    if (!term) return;
    term = term.toLowerCase();

    console.log("Buscando:", term);

    const results = globalBooks.filter(book => {
        const title = book.title.toLowerCase();
        const isbn = book.isbn.toLowerCase();
        const authorName = (book.author.name + " " + book.author.lastname).toLowerCase();

        return title.includes(term) || isbn.includes(term) || authorName.includes(term);
    });

    const container = document.getElementById('searchResultsContainer');
    const template = document.getElementById('book-card-template');

    container.innerHTML = '';

    results.forEach(libro => {
        renderCard(libro, container, template);
    });

    const noResults = document.getElementById('noResultsMessage');
    if (results.length === 0) {
        noResults.style.display = 'block';
    } else {
        noResults.style.display = 'none';
    }

    toggleSearchView(true);
}

// Cambia entre la vista normal y los resultados de buscar
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

// Crea las estrellitas de la valoracion con HTML
function getEstrellasHTML(rating) {
    let html = '';
    for (let i = 1; i <= 5; i++) {
        if (rating >= i) {
            html += '<i class="fa-solid fa-star"></i>';
        } else if (rating >= i - 0.5) {
            html += '<i class="fa-solid fa-star-half-stroke"></i>';
        } else {
            html += '<i class="fa-regular fa-star"></i>';
        }
    }
    return html;
}

// Trae todos los libros de la API y los guarda en la variable global
async function cargarLibrosDesdeBD() {

    try {
        const data = await apiFetch('../../api/GetAllBooks.php');
        console.log("Respuesta GetAllBooks:", data);

        const books = data.data || [];
        globalBooks = books;
        renderBooks(books);
    } catch (error) {
        console.error("Error:", error);
    }
}
// Pinta los libros en las secciones de la portada
function renderBooks(listaLibros) {
    rngBooksRender(listaLibros);
    ratingBooksRender(listaLibros);
}

// Muestra libros al azar en la seccion de descubrimiento
function rngBooksRender(listaLibros) {
    const contenedor = document.getElementById('rngBooksContainer');
    const template = document.getElementById('book-card-template');

    contenedor.innerHTML = '';

    // sort() cambia el array original; despues se reordena otra vez por rating.
    listaLibros.sort(() => Math.random() - 0.5);

    listaLibros.forEach(libro => {
        renderCard(libro, contenedor, template);
    });
}

// Muestra los cuatro libros con mejor puntuacion
function ratingBooksRender(listaLibros) {
    const contenedor = document.getElementById('ratingBooksContainer');
    const template = document.getElementById('book-card-template');
    contenedor.innerHTML = '';
    listaLibros.sort((a, b) => b.rating - a.rating);
    const mejorValorados = listaLibros.slice(0, 4);
    mejorValorados.forEach(libro => {
        renderCard(libro, contenedor, template);
    });
}

// Clona la plantilla de tarjeta y la rellena con los datos del libro
function renderCard(libro, contenedor, template) {
    const clone = template.content.cloneNode(true);

    clone.querySelector('.book-title').textContent = libro.title;
    const author = libro.author.name + " " + libro.author.lastname;
    clone.querySelector('.book-author').textContent = author.trim() === "" ? "Autor Desconocido" : author;
    clone.querySelector('.book-price').textContent = `${libro.price}€`;
    clone.querySelector('.book-stock').textContent = `Stock: ${libro.stock}`;

    const imgElement = clone.querySelector('.book-image-card');
    imgElement.src = "../assets/img/covers/" + (libro.cover || "Book&Bugs_Logo.png");
    imgElement.alt = `Portada de ${libro.title}`;

    const ratingContainer = clone.querySelector('.book-rating');
    ratingContainer.innerHTML = getEstrellasHTML(libro.rating);

    const bookcard = clone.querySelector('.book-card');
    bookcard.onclick = () => {
        window.location.href = `bookDetails.html?isbn=${libro.isbn}`;
    };
    contenedor.appendChild(clone);
}
