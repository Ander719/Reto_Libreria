import { checkSession, currentUser , logout } from './session.js';

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
    const btnLogout = document.getElementById('btnLogout');
    if (btnLogout) {
        btnLogout.addEventListener('click', (e) => {
            e.preventDefault(); // Evita que ponga # en la URL
            logout(); // Llamamos a la función de sesion.js
        });
    }

    actualizarHeader(isLogged);
    cargarLibrosDesdeBD();
}

/**
 * Función que maneja la lógica visual del Header
 */
function actualizarHeader(isLogged) {
    // Seleccionamos los elementos del DOM
    const welcomeText = document.querySelector('.welcome-text');
    const navItems = document.querySelectorAll('.nav-menu li');
    // navItems[0] es "Iniciar Sesión"
    // navItems[1] es "Opciones", navItems[2] es "Cerrar Sesión", etc.

    if (isLogged && currentUser) {
        // --- MODO USUARIO LOGUEADO ---
        welcomeText.textContent = `${currentUser.nombre}`;

        // Ocultamos "Iniciar Sesión"
        navItems[0].hidden = true;

        // Mostramos el resto de opciones (quitamos el atributo hidden)
        navItems.forEach((item, index) => {
            if (index > 0) item.hidden = false;
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
        clone.querySelector('.book-title').textContent = libro.titulo;
        clone.querySelector('.book-author').textContent = libro.autor;
        clone.querySelector('.book-price').textContent = `${libro.price}€`;
        clone.querySelector('.book-stock').textContent = `Stock: ${libro.stock}`;

        // Imagen (si no tienes imagen real, usa un placeholder)
        const imgElement = clone.querySelector('.book-image-card');
        imgElement.src = "../assets/img/" + (libro.img || "mood-heart.png");
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