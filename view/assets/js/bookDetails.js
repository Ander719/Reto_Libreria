import { currentUser, checkSession } from './session.js';
import { loadHeader } from './header.js';
let isEditing = false;

// --- CONFIGURACIÓN DEL MODAL ---
const dialog = document.getElementById('myDialog');
const dialogTitle = document.getElementById('dialogTitle');
const dialogMessage = document.getElementById('dialogMessage');
const closeBtn = document.getElementById('closeDialogBtn');
const confirmBtn = document.getElementById('confirmDialogBtn');

let confirmResolver = null;

document.addEventListener("DOMContentLoaded", async () => {
    console.log("Verificando sesión con el servidor...");

    // Esto ejecutará el fetch a PHP. Si devuelve true, currentUser ya tendrá datos.
    const isLogged = await checkSession();

    if (isLogged) {
        console.log("Usuario logueado:", currentUser);
    } else {
        console.log("No hay sesión activa");
    }
    // 1. Obtener el ISBN de la URL
    // Esto lee lo que hay después del signo ? (ej: ?isbn=1234)
    const params = new URLSearchParams(window.location.search);
    const isbn = params.get('isbn');

    if (!isbn) {
        // CAMBIO AQUÍ
        showModal("Error", "No se ha especificado un libro.");

        // Damos un pequeño tiempo antes de redirigir para que se lea (opcional)
        setTimeout(() => window.location.href = "main.html", 2000);
        return;
    }
    // Cargar el header
    await loadHeader(currentUser);

    // 1. Cargar detalles del libro (Tu lógica actual)...
    loadBookDetails(isbn);
    await checkSession();
    // 2. Gestionar la zona de comentarios
    handleCommentSection();

    // 3. Cargar comentarios existentes
    loadComments(isbn);
});

// 1. Configurar botón de CERRAR (Cancelar)
if (closeBtn) {
    closeBtn.addEventListener('click', () => {
        dialog.close();
        if (confirmResolver) {
            confirmResolver(false); // Resuelve FALSE (Cancelado)
            confirmResolver = null;
        }
    });
}

// 2. Configurar botón de CONFIRMAR (Aceptar)
if (confirmBtn) {
    confirmBtn.addEventListener('click', () => {
        dialog.close();
        if (confirmResolver) {
            confirmResolver(true); // Resuelve TRUE (Aceptado)
            confirmResolver = null;
        }
    });
}


// Función reutilizable
function showModal(titulo, mensaje) {
    dialogTitle.innerText = titulo;
    dialogMessage.innerText = mensaje;

    // Configuración visual: Solo botón cerrar
    closeBtn.innerText = "Cerrar";
    closeBtn.style.display = "inline-block";
    confirmBtn.style.display = "none";

    dialog.showModal();
}

// 3. Función para Confirmaciones (Sustituye a confirm)
function showConfirm(titulo, mensaje, textoConfirmar = "Confirmar", textoCancelar = "Cancelar") {
    dialogTitle.innerText = titulo;
    dialogMessage.innerText = mensaje;

    // Configuración visual: Ambos botones
    closeBtn.innerText = textoCancelar;
    closeBtn.style.display = "inline-block";
    confirmBtn.innerText = textoConfirmar;
    confirmBtn.style.display = "inline-block";

    dialog.showModal();

    // Devolvemos una promesa que espera a que pulsen un botón
    return new Promise((resolve) => {
        confirmResolver = resolve;
    });
}

async function loadBookDetails(isbn) {
    try {
        const response = await fetch(`../../api/GetBook.php?isbn=${isbn}`, { method: 'GET' });
        const rawText = await response.text();

        let data;
        try {
            data = JSON.parse(rawText);
        } catch (error) {
            console.error("❌ El servidor no devolvió JSON. Devolvió esto:\n", rawText);
            return; // Salimos de la función    
        }

        console.log("Datos recibidos del servidor:", data);
        if (data.exito) {
            rellenarVista(data.libro);
        } else {
            document.querySelector('.details-info').innerHTML = "<h2>Libro no encontrado</h2>";
        }

    } catch (error) {
        console.error("Error:", error);
    }
}
/*
async function loadBookDetails(isbn) {
    try {
        const response = await fetch(`../../api/GetBook.php?isbn=${isbn}`);
        if (!response.ok) throw new Error('Book not found');

        const book = await response.json();

        document.getElementById('bookTitle').innerText = book.title || "Untitled";

        let authorName = "Unknown Author";
        if (book.NameAuthor || book.LastName) {
            authorName = `${book.NameAuthor || ''} ${book.LastName || ''}`.trim();
        }
        document.getElementById('bookAuthor').innerText = authorName;

        document.getElementById('bookPrice').innerText = (book.price || 0) + "€";
        document.getElementById('bookSynopsis').innerText = book.sipnosis || "No description available.";

        document.getElementById('bookISBN').innerText = book.Isbn || isbn;
        document.getElementById('bookPages').innerText = book.pages || "N/A";
        document.getElementById('bookEditorial').innerText = book.editorial || "N/A";

        const stockBadge = document.getElementById('stockBadge');
        if (stockBadge) {
            stockBadge.innerText = (book.stock > 0) ? "In Stock" : "Out of Stock";
            stockBadge.className = (book.stock > 0) ? "stock-badge" : "stock-badge out-of-stock";
        }

        if (book.cover) {
            if (book.cover.startsWith('http')) {
                document.getElementById('bookCover').src = book.cover;
            } else {
                document.getElementById('bookCover').src = `../assets/img/${book.cover}`;
            }
        }

    } catch (error) {
        console.error("Error loading book details:", error);
        document.getElementById('bookTitle').innerText = "Book not found";
    }
}
*/
function rellenarVista(libro) {
    // A. Textos Básicos
    document.getElementById('bookTitle').textContent = libro.title || "Título Desconocido";
    document.getElementById('bookAuthor').textContent = (libro.name_author || "Autor Desconocido") + " " + (libro.last_name || "");
    document.getElementById('bookPrice').textContent = parseFloat(libro.price).toFixed(2) + "€";
    document.getElementById('bookSynopsis').textContent = libro.synopsis || "Sin descripción disponible.";

    // B. Metadatos
    document.getElementById('bookISBN').textContent = libro.isbn;
    document.getElementById('bookPages').textContent = libro.pages;
    document.getElementById('bookEditorial').textContent = libro.editorial;

    // C. Imagen (con fallback si falla)
    const img = document.getElementById('bookCover');
    img.src = libro.cover ? `../assets/img/covers/${libro.cover}` : "../assets/img/mood-heart.png"; console.log("Cargando imagen de portada:", img.src);
    img.alt = `Portada de ${libro.title}`;

    // D. Lógica de Stock
    const badge = document.getElementById('stockBadge');
    const btnCart = document.getElementById('addToCartBtn');
    const qtyInput = document.getElementById('qtyInput');

    if (libro.stock > 0) {
        badge.textContent = "In Stock";
        badge.className = "stock-badge success"; // Asegúrate de tener estilo verde en CSS
        badge.style.color = "green";

        // Configurar máximo del input según stock real
        qtyInput.max = libro.stock;
    } else {
        badge.textContent = "Out of Stock";
        badge.className = "stock-badge error";
        badge.style.color = "red";

        // Desactivar compra
        btnCart.disabled = true;
        btnCart.textContent = "Agotado";
        btnCart.style.backgroundColor = "#ccc";
        qtyInput.disabled = true;
    }

    // E. Evento del Botón Añadir al Carrito
    // CAMBIO: Cambiar texto del botón
    btnCart.textContent = "Comprar Ahora";

    // E. NUEVO Evento de Compra Directa
    // Clonamos el botón para borrar cualquier evento anterior (limpieza)
    const newBtn = btnCart.cloneNode(true);
    btnCart.parentNode.replaceChild(newBtn, btnCart);

    newBtn.addEventListener('click', async () => {
        if (!currentUser) {
            showModal("Atención", "Debes iniciar sesión para comprar.");
            return;
        }

        const cantidad = parseInt(qtyInput.value);

        const aceptado = await showConfirm(
            "Confirmar Compra",
            "¿Estás seguro de que quieres comprar este libro?",
            "Si, comprar",
            "Cancelar"
        );

        if (aceptado) {
            const userId = currentUser.profile_code || currentUser.id;
            comprarAhora(libro.isbn, cantidad, userId);
        }
    });
}
function agregarAlCarrito(isbn, cantidad) {
    // Aquí iría tu fetch a api/AddToCart.php
    console.log(`Añadiendo ${cantidad} copia(s) del libro ${isbn} al carrito.`);
    alert("Producto añadido al carrito (Simulación)");
}

function handleCommentSection() {
    const actionContainer = document.getElementById('userActionContainer');
    const loginPrompt = document.getElementById('loginPrompt');
    const formName = document.querySelector('#userActionContainer strong');

    if (currentUser) {

        actionContainer.hidden = false;
        loginPrompt.hidden = true;

        formName.textContent = currentUser.name;

        // Listener
        document.getElementById('commentForm').addEventListener('submit', (e) => submitComment(e, currentUser));
        document.getElementById('cancelEditBtn').addEventListener('click', () => {
            resetForm();
        });

    } else {
        actionContainer.hidden = true;
        loginPrompt.hidden = false;
    }
}

async function submitComment(e, user) {
    e.preventDefault();

    const rating = document.getElementById('ratingScore').value;
    const text = document.getElementById('commentBody').value;
    const messageSpan = document.getElementById('formMessage');
    const urlParams = new URLSearchParams(window.location.search);
    const isbn = urlParams.get('isbn');

    messageSpan.className = '';
    messageSpan.innerText = '';
    messageSpan.style.display = 'block';

    if (!text || !isbn) {
        messageSpan.innerText = "Please complete all fields.";
        messageSpan.classList.add('msg-error');
        return;
    }

    console.log("🕵️ OBJETO USUARIO:", user);

    const profileCode = getUserId(user);
    // Comprobación de seguridad
    if (!profileCode) {
        console.error("❌ Error: No se encuentra el ID del usuario en:", user);
        alert("Error de sesión. Por favor, recarga la página e inicia sesión de nuevo.");
        return;
    }
    const url = isEditing ? '../../api/UpdateComment.php' : '../../api/AddComment.php';

    const payload = {
        profileCode: profileCode,
        isbn: isbn,
        comment: text,
        text: text,
        rating: rating,
        valoration: rating,
        date: new Date().toISOString().slice(0, 10)
    };

    console.log("Sending payload:", payload);

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if (response.ok) {
            messageSpan.innerText = isEditing ? "Updated successfully!" : "Posted successfully!";
            messageSpan.classList.add('msg-success');

            resetForm();
            loadComments(isbn);

            setTimeout(() => {
                messageSpan.innerText = '';
                messageSpan.className = '';
            }, 3000);

        } else {
            const errorText = await response.text();
            console.error("Server Error:", errorText);

            try {
                const errJson = JSON.parse(errorText);
                messageSpan.innerText = errJson.message || "Error saving review.";
            } catch (e) {
                messageSpan.innerText = "Error (Maybe duplicate review?)";
            }
            messageSpan.classList.add('msg-error');
        }
    } catch (error) {
        console.error(error);
        messageSpan.innerText = "Connection error.";
        messageSpan.classList.add('msg-error');
    }
}

async function loadComments(isbn) {
    const commentsList = document.getElementById('commentsList');
    // Usamos el ID del usuario actual si existe
    const myProfileCode = getUserId(currentUser);

    try {
        const response = await fetch(`../../api/GetComments.php?isbn=${isbn}`);
        const rawText = await response.text();

        let comments;
        try {
            comments = JSON.parse(rawText);
        } catch (error) {
            console.error("❌ El servidor no devolvió JSON. Devolvió esto:\n", rawText);
            commentsList.innerHTML = '<p class="error-msg">Error loading comments.</p>';
            return; // Salimos de la función
        }

        console.log("Datos recibidos del servidor:", comments);
        commentsList.innerHTML = '';
        let myReview = null;

        if (comments.length > 0) {
            comments.forEach(c => {
                const item = document.createElement('div');
                item.classList.add('comment-item');

                // Comprobamos si este comentario es mío
                const isMine = myProfileCode && (parseInt(c.PROFILE_CODE) === parseInt(myProfileCode));
                console.log("Comentario recibido:", c);
                if (isMine) myReview = c;

                // Generamos los botones (Lápiz y Basura) solo si es mío
                let buttonsHtml = '';
                if (isMine) {
                    // Escapamos las comillas simples para que no rompan el HTML del onclick
                    const safeText = c.comment_text.replace(/'/g, "\\'");
                    buttonsHtml = `
                        <div class="comment-actions">
                            <button onclick="startEdit('${safeText}', ${c.valoration})" class="btn-icon" title="Edit">✏️</button>
                            <button onclick="deleteComment('${isbn}')" class="btn-icon btn-delete" title="Delete">🗑️</button>
                        </div>
                    `;
                }

                item.innerHTML = `
                    ${buttonsHtml}
                    <p class="comment-header">
                        ${c.USER_NAME} 
                        <span class="comment-date">${c.dateComent}</span>
                    </p>
                    <div class="star-rating">${'⭐'.repeat(c.valoration)}</div>
                    <p class="comment-text">${c.comment_text}</p> <div class="clear-fix"></div>
                `;
                commentsList.appendChild(item);
            });
        } else {
            commentsList.innerHTML = '<p class="no-reviews">No reviews yet. Be the first to write one!</p>';
        }

        // --- AQUÍ ESTÁ LA LÓGICA QUE PIDES ---
        const actionContainer = document.getElementById('userActionContainer');

        if (myReview) {
            // SI YA COMENTÉ: Borro todo lo de abajo. Limpio total.
            actionContainer.innerHTML = '';
        } else {
            // SI NO HE COMENTADO: Muestro el formulario (si no está ya pintado)
            if (!document.getElementById('commentForm')) {
                handleCommentSection();
            }
        }

    } catch (error) {
        console.error(error);
    }
}

window.deleteComment = async function (isbn) {
    const aceptado = await showConfirm(
        "Borrar Reseña",
        "¿Estás seguro de que quieres eliminar tu reseña? Esta acción no se puede deshacer.",
        "Borrar",
        "Volver"
    );

    if (!aceptado) return;

    // PROTECCIÓN: Si no hay usuario cargado, paramos
    if (!currentUser) {
        showModal("Error", "No se detecta la sesión. Recarga la página.");
        return;
    }

    // Obtenemos el ID de forma segura
    const profileCode = getUserId(currentUser);
    try {
        const response = await fetch('../../api/DeleteComment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ isbn: isbn, profileCode: profileCode })
        });

        if (response.ok) {
            showModal("Éxito", "Comentario eliminado correctamente.");
            await loadComments(isbn);
            if (document.getElementById('commentBody')) {
                resetForm();
            }

        } else {
            showModal("Error", "No se pudo eliminar el comentario.");
        }
    } catch (error) {
        console.error("Error en deleteComment:", error);
        showModal("Error de conexión", "Mira la consola para más detalles.");
    }
};

window.startEdit = function (text, rating, doScroll = true) {
    // 1. SI EL FORMULARIO NO EXISTE, LO VOLVEMOS A PINTAR
    if (!document.getElementById('commentForm')) {
        handleCommentSection();
    }

    // 2. ACTIVAMOS MODO EDICIÓN
    isEditing = true;
    document.getElementById('commentBody').value = text;
    document.getElementById('ratingScore').value = parseInt(rating);

    const title = document.getElementById('formTitle');
    const submitBtn = document.getElementById('submitBtn');
    if (title) title.innerText = "Edit your Review";
    if (submitBtn) submitBtn.innerText = "Update Review";

    // 3. CONFIGURAMOS EL BOTÓN CANCELAR
    const cancelBtn = document.getElementById('cancelEditBtn');
    if (cancelBtn) {
        cancelBtn.style.display = "inline-block";

        cancelBtn.onclick = () => {
            isEditing = false;
            // Borramos todo al cancelar para dejarlo limpio
            document.getElementById('userActionContainer').innerHTML = '';
        };
    }

    if (doScroll) {
        document.getElementById('userActionContainer').scrollIntoView({ behavior: 'smooth' });
    }
};

function resetForm() {
    isEditing = false;
    document.getElementById('commentBody').value = "";
    document.getElementById('formTitle').innerText = "Write a Review";
    document.getElementById('submitBtn').innerText = "Submit Review";
    document.getElementById('cancelEditBtn').style.display = "none";
    document.getElementById('formMessage').innerText = "";
    document.getElementById('formMessage').className = "";
}



async function comprarAhora(isbn, quantity, userId) {
    try {
        const response = await fetch('../../api/BuyNow.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                profileCode: userId,
                isbn: isbn,
                quantity: quantity
            })
        });

        // Parseamos la respuesta con seguridad
        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error("Respuesta no válida:", text);
            showModal("Error", "Error inesperado del servidor.");
            return;
        }

        if (data.exito) {
            // AQUÍ ESTABA EL ALERT, LO CAMBIAMOS POR MODAL
            showModal("¡Compra realizada!", "Gracias por tu pedido. Disfruta tu lectura.");

            // Opcional: Actualizar la página tras 2 segundos para ver el stock bajar
            // setTimeout(() => location.reload(), 2000); 
        } else {
            showModal("Error", data.error || "No se pudo completar la compra.");
        }

    } catch (error) {
        console.error(error);
        showModal("Error de conexión", "No se pudo contactar con el servidor.");
    }
}


// Función auxiliar para obtener el ID, venga como venga
export function getUserId(user) {
    if (!user) return null;
    return user.profile_code || user.id || user.PROFILE_CODE;
}


