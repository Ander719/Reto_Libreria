import { checkSession, currentUser} from './session.js';
import { loadHeader } from './header.js';

init();

let globalBooks = [];

async function init() {
    console.log("Verificando sesión con el servidor...");

    // Esto ejecutará el fetch a PHP. Si devuelve true, currentUser ya tendrá datos.
    const isLogged = await checkSession();

    if (isLogged) {
        console.log("Usuario logueado:", currentUser);
    } else {
        console.log("No hay sesión activa");
    }
    loadHeader("main");
    cargarLibrosDesdeBD();
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
        const response = await fetch('../../api/GetAllBooks.php'); // Tu nueva API
        console.log(response);

        const rawText = await response.text();

        let data;
        try {
            data = JSON.parse(rawText);
        } catch (error) {
            console.error("❌ El servidor no devolvió JSON. Devolvió esto:\n", rawText);
            return; // Salimos de la función
        }

        console.log("Datos recibidos:", data);

        if (data.exito) {
            globalBooks = data.libros; // Guardamos los libros globalmente si es necesario
            renderizarLibros(data.libros);
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
function renderizarLibros(listaLibros) {
    rngBooksRender(listaLibros);
    ratingBooksRender(listaLibros);
    newBooksRender(listaLibros);
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
// Función para renderizar los libros recientes
function newBooksRender(listaLibros) {
    const contenedor = document.getElementById('newbooksContainer');
    const template = document.getElementById('book-card-template');
    // Limpiamos el contenedor por si acaso
    contenedor.innerHTML = '';
    //ordenamos 4 libros por fecha de publicación (los más recientes primero)
    listaLibros.sort((a, b) => new Date(b.publish_date) - new Date(a.publish_date));
    //tomamos solo los 4 primeros
    const recientes = listaLibros.slice(0, 4);
    recientes.forEach(libro => {
        renderCard(libro, contenedor, template);
    });
}
function renderCard(libro, contenedor, template) {
    // Clonamos el contenido del template
    const clone = template.content.cloneNode(true);

    // Rellenamos los datos buscando por clase dentro del clon
    clone.querySelector('.book-title').textContent = libro.title;
    const author = libro.author.name + " " + libro.author.lastname;
    clone.querySelector('.book-author').textContent = author.trim().isempty ? "Autor Desconocido" : author;
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