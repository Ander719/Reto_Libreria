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
    await loadHeader("book_view");

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

function rellenarVista(libro) {
    document.getElementById('bookTitle').textContent = libro.title || "Título Desconocido";
    document.getElementById('bookAuthor').textContent = (libro.name_author || "Autor Desconocido") + " " + (libro.last_name || "");
    document.getElementById('bookPrice').textContent = parseFloat(libro.price).toFixed(2) + "€";
    document.getElementById('bookSynopsis').textContent = libro.synopsis || "Sin descripción disponible.";

    document.getElementById('bookISBN').textContent = libro.isbn;
    document.getElementById('bookPages').textContent = libro.pages;
    document.getElementById('bookEditorial').textContent = libro.editorial;

    const img = document.getElementById('bookCover');
    img.src = libro.cover ? `../assets/img/covers/${libro.cover}` : "../assets/img/mood-heart.png"; console.log("Cargando imagen de portada:", img.src);
    img.alt = `Portada de ${libro.title}`;

    const badge = document.getElementById('stockBadge');
    const btnCart = document.getElementById('addToCartBtn');
    const qtyInput = document.getElementById('qtyInput');

    if (libro.stock > 0) {
        badge.textContent = "In Stock";
        badge.className = "stock-badge success";
        badge.style.color = "green";

        qtyInput.max = libro.stock;
    } else {
        badge.textContent = "Out of Stock";
        badge.className = "stock-badge error";
        badge.style.color = "red";

        btnCart.disabled = true;
        btnCart.textContent = "Agotado";
        btnCart.style.backgroundColor = "#ccc";
        qtyInput.disabled = true;
    }


    btnCart.textContent = "Comprar Ahora";


    const newBtn = btnCart.cloneNode(true);
    btnCart.parentNode.replaceChild(newBtn, btnCart);

    newBtn.addEventListener('click', async () => {
        if (!currentUser) {
            showModal("Atención", "Debes iniciar sesión para comprar.");
            return;
        }

        const userCard = currentUser.card_no || currentUser.CardNo;

        if (!userCard || userCard.trim() === "") {
            const quiereAnadir = await showConfirm(
                "Método de pago no encontrado",
                "No tienes una tarjeta vinculada para realizar compras. ¿Quieres ir a tu perfil para añadir una ahora?",
                "Sí, ir al perfil",
                "No, volver a la tienda"
            );

            if (quiereAnadir) {
                window.location.href = "configProfile.html";
            } else {
                window.location.href = "store.html";
            }

            return;
        }

        const cantidad = parseInt(qtyInput.value);

        const aceptado = await showConfirm(
            "Confirmar Compra",
            "¿Estás seguro de que quieres comprar este libro?",
            "Sí, comprar",
            "Cancelar"
        );

        if (aceptado) {
            const userId = getUserId(currentUser);
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

    // CAMBIO: Añadimos && !currentUser.isAdmin
    if (currentUser && !currentUser.isAdmin) {

        // --- USUARIO NORMAL: VE EL FORMULARIO ---
        actionContainer.hidden = false;
        loginPrompt.hidden = true;

        formName.textContent = currentUser.name;

        // Listener (usamos onclick para evitar acumulación de listeners si se llama varias veces)
        const form = document.getElementById('commentForm');
        if (form) {
            form.onsubmit = (e) => submitComment(e, currentUser);
        }

        const cancelBtn = document.getElementById('cancelEditBtn');
        if (cancelBtn) {
            cancelBtn.onclick = () => resetForm();
        }

    } else if (currentUser && currentUser.isAdmin) {

        // --- ADMIN: NO VE NADA (Ni formulario, ni aviso de login) ---
        actionContainer.hidden = true;
        loginPrompt.hidden = true; // Opcional: Puedes ponerlo false y cambiar el texto a "Modo Admin"

    } else {

        // --- NO LOGUEADO: VE EL AVISO DE "INICIA SESIÓN" ---
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

// --- FUNCIÓN CORREGIDA Y ARREGLADA ---
async function loadComments(isbn) {
    const commentsList = document.getElementById('commentsList');
    const myProfileCode = getUserId(currentUser);

    try {
        const response = await fetch(`../../api/GetComments.php?isbn=${isbn}`);
        const rawText = await response.text();
        let comments;
        try { comments = JSON.parse(rawText); } catch (e) { console.error("Error JSON", rawText); return; }

        commentsList.innerHTML = '';
        let myReview = null;

        if (comments.length > 0) {
            comments.forEach((c) => {
                const item = document.createElement('div');
                item.classList.add('comment-item');

                const authorId = c.profile_code || c.PROFILE_CODE;
                const isMine = myProfileCode && authorId && (parseInt(authorId) === parseInt(myProfileCode));
                // Verificamos si es Admin (flag enviado desde CheckSession.php)
                const isAdmin = currentUser && currentUser.isAdmin === true;

                if (isMine) myReview = c;

                let buttonsHtml = '';

                // CASO 1: ES MÍO (Editar + Borrar)
                if (isMine) {
                    // Escapamos comillas simples, dobles y saltos de línea para que no rompa el botón
                    const safeText = c.comment_text
                        ? c.comment_text.replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, '\\n')
                        : ""; buttonsHtml = `
                        <div class="comment-actions">
                            <button onclick="startEdit('${safeText}', ${c.valoration})" class="btn-icon" title="Editar">✏️</button>
                            <button onclick="deleteComment('${isbn}', '${authorId}')" class="btn-icon btn-delete" title="Borrar">🗑️</button>
                        </div>
                    `;
                }
                // CASO 2: SOY ADMIN Y NO ES MÍO (Solo Borrar)
                else if (isAdmin) {
                    buttonsHtml = `
                        <div class="comment-actions">
                            <button onclick="deleteComment('${isbn}', '${authorId}')" class="btn-icon btn-delete" title="Borrar como Admin">🗑️</button>
                        </div>
                    `;
                }

                const userName = c.user_name || c.USER_NAME || "Usuario";
                const commentText = c.comment_text || c.COMMENT_TEXT || "";
                const date = c.dateComent || c.DATE_COMENT || "";

                item.innerHTML = `
                    ${buttonsHtml}
                    <div class="comment-header">
                        <strong>${userName}</strong>
                        <span class="comment-date">${date}</span>
                    </div>
                    <div class="star-rating">${'⭐'.repeat(c.valoration || 0)}</div>
                    <p class="comment-text">${commentText}</p> 
                    <div class="clear-fix"></div>
                `;
                commentsList.appendChild(item);
            });
        } else {
            commentsList.innerHTML = '<p class="no-reviews" style="text-align:center; color:#999;">No hay reseñas todavía.</p>';
        }

        // Ocultar formulario si ya he comentado (SIN BORRARLO)
        const actionContainer = document.getElementById('userActionContainer');
        const form = document.getElementById('commentForm');

        // 1. Limpiamos mensaje de "ya comentado" si existía de antes
        const msgExistente = document.getElementById('msg-review-exists');
        if (msgExistente) msgExistente.remove();

        if (myReview) {
            // Si ya comenté: OCULTO el formulario (no lo borro)
            if (form) form.style.display = 'none';

            // Y creo el mensaje de texto con un ID para poder quitarlo luego al editar
            const p = document.createElement('p');
            p.id = 'msg-review-exists';
            p.className = 'info-msg';
            p.style.cssText = "text-align:center; padding:10px; color:#28a745;";
            p.innerText = "Ya has publicado una reseña para este libro.";
            actionContainer.appendChild(p);

            actionContainer.hidden = false;
        } else {
            // Si no he comentado: ASEGURO que se vea el formulario
            if (form) form.style.display = 'block';

            // Si por algún casual no existe (primera carga sin login), lo generamos
            if (!form && currentUser) handleCommentSection();
        }
    } catch (error) {
        console.error("Error loadComments:", error);
    }
}
// --- FUNCIÓN DE BORRADO ---
window.deleteComment = async function (isbn, targetProfileCode) {
    const aceptado = await showConfirm("Borrar", "¿Seguro que quieres borrar este comentario?");
    if (!aceptado) return;

    if (!currentUser) return;

    // Usamos el ID del autor del comentario si viene, sino el mío.
    const codeToDelete = targetProfileCode || getUserId(currentUser);

    try {
        const response = await fetch('../../api/DeleteComment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                isbn: isbn,
                profileCode: codeToDelete
            })
        });

        if (response.ok) {
            showModal("Éxito", "Comentario eliminado.");
            // Si borré el mío, recargo para poder comentar de nuevo
            if (parseInt(codeToDelete) === parseInt(getUserId(currentUser))) {
                setTimeout(() => location.reload(), 1000);
            } else {
                await loadComments(isbn);
            }
        } else {
            showModal("Error", "No se pudo eliminar.");
        }
    } catch (error) {
        console.error(error);
    }
};
window.startEdit = function (text, rating, doScroll = true) {
    isEditing = true;

    const form = document.getElementById('commentForm');
    const msg = document.getElementById('msg-review-exists');
    const container = document.getElementById('userActionContainer');

    // 1. Si el formulario no existe (seguridad), recargamos
    if (!form) {
        console.error("Formulario no encontrado. Recargando...");
        location.reload();
        return;
    }

    // 2. Preparamos la vista: Ocultar mensaje de "Ya comentado", Mostrar form
    if (msg) msg.style.display = 'none';
    form.style.display = 'block';
    container.hidden = false;

    // 3. Rellenamos los datos
    document.getElementById('commentBody').value = text;
    document.getElementById('ratingScore').value = parseInt(rating);

    // 4. Ajustamos textos de botones
    document.getElementById('formTitle').innerText = "Editar Reseña";
    document.getElementById('submitBtn').innerText = "Actualizar";

    const cancelBtn = document.getElementById('cancelEditBtn');
    cancelBtn.style.display = "inline-block";

    // 5. Configurar botón Cancelar para restaurar el estado anterior
    cancelBtn.onclick = () => {
        isEditing = false;
        resetForm();
        // Si había mensaje de "ya comentado", volvemos a mostrarlo y ocultamos form
        if (msg) {
            msg.style.display = 'block';
            form.style.display = 'none';
        }
    };

    if (doScroll) {
        container.scrollIntoView({ behavior: 'smooth' });
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
    return user.profile_code;
}


