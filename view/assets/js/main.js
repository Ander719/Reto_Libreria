import { checkSession, currentUser, logout } from './session.js';
import { loadHeader } from './header.js';

init();

async function init() {
    console.log("Verificando sesión con el servidor...");

    // Esto ejecutará el fetch a PHP. Si devuelve true, currentUser ya tendrá datos.
    const isLogged = await checkSession();

    if (isLogged) {
        console.log("Usuario logueado:", currentUser);
    } else {
        console.log("No hay sesión activa");
    }
    document.querySelector('.nav-menu').addEventListener('click', (event) => {
        if (event.target && event.target.id === 'btnLogout') {
            event.preventDefault(); // Prevenir el comportamiento por defecto del enlace
            logout(); // Llamar a la función de logout
        }
        if (event.target && event.target.textContent === 'Opciones') {
            event.preventDefault(); // Prevenir el comportamiento por defecto del enlace
            if (!isLogged) return; // Si no está logueado, no hacer nada
            if (currentUser.role === 'admin') {
                window.location.href = 'opcAdmin.html';
            } else {
                window.location.href = 'configProfile.html';
            }
        }
    });


    loadHeader(currentUser);
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
    const contenedor = document.getElementById('productGrid'); // Tu div product-grid
    const template = document.getElementById('book-card-template');

    // Limpiamos el contenedor por si acaso
    contenedor.innerHTML = '';

    listaLibros.forEach(libro => {
        // Clonamos el contenido del template
        const clone = template.content.cloneNode(true);

        // Rellenamos los datos buscando por clase dentro del clon
        clone.querySelector('.book-title').textContent = libro.title;
        clone.querySelector('.book-author').textContent = libro.autor;
        clone.querySelector('.book-price').textContent = `${libro.price}€`;
        clone.querySelector('.book-stock').textContent = `Stock: ${libro.stock}`;

        // Imagen (si no tienes imagen real, usa un placeholder)
        const imgElement = clone.querySelector('.book-image-card');
        imgElement.src = "../assets/img/covers/" + (libro.cover || "mood-heart.png");
        imgElement.alt = `Portada de ${libro.titulo}`;

        // Inyectamos las estrellas
        const ratingContainer = clone.querySelector('.book-rating');
        ratingContainer.innerHTML = getEstrellasHTML(libro.rating);

        // Añadimos el evento click a la tarjeta
        const bookcard = clone.querySelector('.book-image-card');
        bookcard.onclick = () => {
            // Redirigimos pasando el ISBN en la URL
            window.location.href = `bookDetails.html?isbn=${libro.isbn}`;
        };
        // Añadimos la tarjeta al grid
        contenedor.appendChild(clone);
    });
}