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
    await loadHeader("bookDetails");

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
        let userCard;
        try {
            const res = await fetch('../../api/GetProfile.php');
            const data = await res.json();

            if (data.success && data.user) {
                const u = data.user;
                userCard = u.card_no || u.CardNo || u.CARD_NO || u.cardNo;
            }
        } catch (err) { console.error(err); }
        // Intentamos leer la tarjeta con todas las variantes posibles de mayúsculas/minúsculas
        console.log("Tarjeta del usuario:", userCard);
        if (!userCard || userCard.trim() === "") {
            const quiereAnadir = await showConfirm(
                "Método de pago no encontrado",
                "No tienes una tarjeta vinculada para realizar compras. ¿Quieres ir a tu perfil para añadir una ahora?",
                "Sí, ir al perfil",
                "No, volver a la tienda"
            );

            if (quiereAnadir) {
                window.location.href = "configProfile.html";
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
    // Usamos el ID nuevo que pusimos en el HTML
    const nameDisplay = document.getElementById('userNameDisplay');

    // MODO DETECTIVE: Mira en la consola del navegador (F12) qué imprime esto
    if (currentUser) console.log("Datos del usuario:", currentUser);

    if (currentUser && currentUser.role !== "admin") {
        actionContainer.hidden = false;
        loginPrompt.hidden = true;

        // --- CORRECCIÓN AQUÍ ---
        // Intentamos obtener el nombre de varias formas posibles por si acaso
        const realName = currentUser.name || currentUser.user_name || currentUser.nombre || currentUser.username || "Usuario";

        if (nameDisplay) {
            nameDisplay.textContent = realName;
        }
        // -----------------------

        const form = document.getElementById('commentForm');
        if (form) form.onsubmit = (e) => submitComment(e, currentUser);

        const cancelBtn = document.getElementById('cancelEditBtn');
        if (cancelBtn) cancelBtn.onclick = () => resetForm();

    } else if (currentUser && currentUser.role === "admin") {
        actionContainer.hidden = true;
        loginPrompt.hidden = true;
    } else {
        actionContainer.hidden = true;
        loginPrompt.hidden = false;
    }
}
async function submitComment(e) {
    e.preventDefault();

    // Obtenemos el valor del select (que ahora puede ser "4.5")
    const ratingInput = document.getElementById('ratingScore').value;
    const text = document.getElementById('commentBody').value;
    const msg = document.getElementById('formMessage');
    const params = new URLSearchParams(window.location.search);
    const isbn = params.get('isbn');

    if (!text) {
        msg.textContent = "Por favor escribe una reseña.";
        msg.className = "msg-error";
        return;
    }

    const url = isEditing ? '../../api/UpdateComment.php' : '../../api/AddComment.php';

    // CORRECCIÓN: Usamos parseFloat para permitir decimales (4.5)
    const payload = {
        profileCode: getUserId(currentUser),
        isbn: isbn,
        comment: text,
        text: text,
        rating: parseFloat(ratingInput),      // <--- IMPORTANTE: parseFloat
        valoration: parseFloat(ratingInput),  // <--- IMPORTANTE: parseFloat
        date: new Date().toISOString().slice(0, 10)
    };

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if (res.ok) {
            msg.className = "msg-success";
            msg.textContent = isEditing ? "Actualizado correctamente." : "Publicado correctamente.";
            resetForm();
            loadComments(isbn);
            setTimeout(() => msg.textContent = "", 3000);
        } else {
            const err = await res.json();
            msg.className = "msg-error";
            msg.textContent = err.message || "Error al guardar.";
        }
    } catch (e) { console.error(e); }
}

// --- FUNCIÓN CORREGIDA Y ARREGLADA ---
async function loadComments(isbn) {
    const list = document.getElementById('commentsList');
    const myId = getUserId(currentUser);

    try {
        const res = await fetch(`../../api/GetComments.php?isbn=${isbn}`);
        const comments = await res.json(); // Si falla aquí, tu PHP devolvió error

        list.innerHTML = "";
        let myReview = null;

        // --- ORDENAR: Mis comentarios primero ---
        if (myId && comments.length > 0) {
            comments.sort((a, b) => {
                const idA = a.profile_code || a.PROFILE_CODE;
                const idB = b.profile_code || b.PROFILE_CODE;
                if (parseInt(idA) === parseInt(myId)) return -1;
                if (parseInt(idB) === parseInt(myId)) return 1;
                return 0;
            });
        }
        // ----------------------------------------

        if (comments.length > 0) {
            comments.forEach(c => {
                const item = document.createElement('div');
                item.className = 'comment-item';

                const authorId = c.profile_code || c.PROFILE_CODE;
                const isMine = myId && authorId && (parseInt(authorId) === parseInt(myId));
                const isAdmin = currentUser && currentUser.isAdmin === true;

                if (isMine) myReview = c;

                let btnHtml = "";
                // Escapamos comillas para evitar errores en el botón editar
                const safeText = (c.comment_text || "").replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, '\\n');

                if (isMine) {
                    btnHtml = `
                        <div class="comment-actions">
                            <button onclick="startEdit('${safeText}', ${c.valoration})" class="btn-icon">✏️</button>
                            <button onclick="deleteComment('${isbn}', '${authorId}')" class="btn-icon btn-delete">🗑️</button>
                        </div>`;
                } else if (isAdmin) {
                    btnHtml = `
                        <div class="comment-actions">
                            <button onclick="deleteComment('${isbn}', '${authorId}')" class="btn-icon btn-delete">🗑️</button>
                        </div>`;
                }

                // --- AQUÍ ESTÁ EL CAMBIO DE LAS ESTRELLAS ---
                // Usamos getStarHtml y parseFloat
                const starsHtml = getStarHtml(parseFloat(c.valoration));
                // --------------------------------------------

                item.innerHTML = `
                    ${btnHtml}
                    <div class="comment-header">
                        <strong>${c.user_name || "Usuario"}</strong>
                        <span class="comment-date">${c.dateComent || ""}</span>
                    </div>
                    <div class="star-rating">${starsHtml}</div> <p class="comment-text">${c.comment_text || ""}</p>
                    <div class="clear-fix"></div>
                `;
                list.appendChild(item);
            });
        } else {
            list.innerHTML = "<p style='text-align:center'>Sin comentarios.</p>";
        }

        // --- GESTIÓN DEL FORMULARIO (OCULTAR/MOSTRAR) ---
        const actionContainer = document.getElementById('userActionContainer');
        const form = document.getElementById('commentForm');
        const msgExistente = document.getElementById('msg-review-exists');
        if (msgExistente) msgExistente.remove();

        if (myReview) {
            if (form) form.style.display = 'none';
            const p = document.createElement('p');
            p.id = 'msg-review-exists';
            p.className = 'info-msg';
            p.style.cssText = "text-align:center; padding:10px; color:#28a745;";
            p.innerText = "Ya has publicado una reseña para este libro.";
            actionContainer.appendChild(p);
            actionContainer.hidden = false;
        } else {
            if (form) form.style.display = 'block';
            if (!form && currentUser) handleCommentSection();
        }

    } catch (e) { console.error(e); }
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

    if (!form) { location.reload(); return; }

    // Restauramos vista
    if (msg) msg.style.display = 'none';
    form.style.display = 'block';
    container.hidden = false;

    document.getElementById('commentBody').value = text;

    // CORRECCIÓN: Usamos parseFloat para que el select reconozca "4.5"
    document.getElementById('ratingScore').value = parseFloat(rating);

    document.getElementById('formTitle').innerText = "Editar Reseña";
    document.getElementById('submitBtn').innerText = "Actualizar";

    const cancelBtn = document.getElementById('cancelEditBtn');
    cancelBtn.style.display = "inline-block";

    cancelBtn.onclick = () => {
        isEditing = false;
        resetForm();
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
    document.getElementById('formTitle').innerText = "Escribe una reseña"; // Español
    document.getElementById('submitBtn').innerText = "Publicar Reseña";   // Español
    document.getElementById('cancelEditBtn').style.display = "none";
    document.getElementById('formMessage').innerText = "";
    document.getElementById('formMessage').className = "";

    setRating(5);
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



// --- FUNCIÓN NUEVA: DIBUJAR ESTRELLAS ---
function getStarHtml(rating) {
    let html = '';
    const fullStars = Math.floor(rating); // Parte entera (ej: 4.5 -> 4)
    const hasHalf = (rating % 1) >= 0.5;  // ¿Tiene decimal? (ej: 4.5 -> true)

    // Calculamos las vacías (Total 5 - llenas - media)
    const emptyStars = 5 - fullStars - (hasHalf ? 1 : 0);

    // 1. Estrellas enteras
    for (let i = 0; i < fullStars; i++) {
        html += '<span class="full">★</span>';
    }
    // 2. Media estrella (si aplica)
    if (hasHalf) {
        html += '<span class="half">★</span>';
    }
    // 3. Estrellas vacías
    for (let i = 0; i < emptyStars; i++) {
        html += '<span>★</span>';
    }
    return html;
}



// --- 1. FUNCIÓN AUXILIAR PARA MARCAR ESTRELLAS ---
// Esta función se llama al hacer click en una estrella o desde startEdit
window.setRating = function (val) {
    // 1. Guardamos el valor numérico (ej: 4.5)
    const hiddenInput = document.getElementById('ratingScore');
    if (hiddenInput) hiddenInput.value = val;

    // 2. Marcamos el radio button específico (ID dinámico st45, st3, st25...)
    // Quitamos el punto del decimal para el ID: 4.5 -> st45, 4 -> st4
    const starId = 'st' + val.toString().replace('.', '');
    const radioBtn = document.getElementById(starId);
    if (radioBtn) radioBtn.checked = true;

    // 3. Actualizamos el texto visual de la nota
    const textDiv = document.getElementById('rating-text');
    if (textDiv) textDiv.innerText = val + "/5";
};

window.startEdit = function (text, rating, doScroll = true) {
    isEditing = true;

    const form = document.getElementById('commentForm');
    const msg = document.getElementById('msg-review-exists');
    const container = document.getElementById('userActionContainer');
    const submitBtn = document.getElementById('submitBtn');
    const formTitle = document.getElementById('formTitle');
    const commentBody = document.getElementById('commentBody');
    const cancelBtn = document.getElementById('cancelEditBtn');

    if (!form) { location.reload(); return; }

    if (msg) msg.style.display = 'none';
    form.style.display = 'block';
    container.hidden = false;

    if (commentBody) commentBody.value = text;

    // Llamamos a setRating con el float (ej: 4.5)
    setRating(parseFloat(rating));

    // TRADUCCIÓN AQUÍ
    if (formTitle) formTitle.innerText = "Editar Reseña";
    if (submitBtn) submitBtn.innerText = "Actualizar Reseña";

    if (cancelBtn) {
        cancelBtn.style.display = "inline-block";
        cancelBtn.innerText = "Cancelar"; // Aseguramos español
        cancelBtn.onclick = () => {
            isEditing = false;
            resetForm();
            if (msg) {
                msg.style.display = 'block';
                form.style.display = 'none';
            }
        };
    }

    if (doScroll) {
        container.scrollIntoView({ behavior: 'smooth' });
    }
};