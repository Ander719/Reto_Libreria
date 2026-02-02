import { currentUser, checkSession } from './session.js';
<<<<<<< HEAD
import { loadHeader,loadFooter } from './header.js';
=======
import { loadHeader } from './header.js';

>>>>>>> a89ff5259b322456fa712f01948ebd75565b4e70
let isEditing = false;

// --- CONFIGURACIÓN DEL MODAL ---
const dialog = document.getElementById('myDialog');
const dialogTitle = document.getElementById('dialogTitle');
const dialogMessage = document.getElementById('dialogMessage');
const closeBtn = document.getElementById('closeDialogBtn');
const confirmBtn = document.getElementById('confirmDialogBtn');

let confirmResolver = null;

document.addEventListener("DOMContentLoaded", async () => {
    console.log("Verificando sesión...");
    await checkSession();
    await loadHeader("bookDetails");

    const params = new URLSearchParams(window.location.search);
    const isbn = params.get('isbn');

    if (!isbn) {
        showModal("Error", "No se ha especificado un libro.");
        setTimeout(() => window.location.href = "main.html", 2000);
        return;
    }
<<<<<<< HEAD
    // Cargar el header
    await loadHeader("bookDetails");
    await loadFooter();
=======
>>>>>>> a89ff5259b322456fa712f01948ebd75565b4e70

    loadBookDetails(isbn);
    handleCommentSection();
    loadComments(isbn);
});

// Configuración de botones del modal
if (closeBtn) {
    closeBtn.addEventListener('click', () => {
        dialog.close();
        if (confirmResolver) { confirmResolver(false); confirmResolver = null; }
    });
}

if (confirmBtn) {
    confirmBtn.addEventListener('click', () => {
        dialog.close();
        if (confirmResolver) { confirmResolver(true); confirmResolver = null; }
    });
}

function showModal(titulo, mensaje) {
    dialogTitle.innerText = titulo;
    dialogMessage.innerText = mensaje;
    closeBtn.innerText = "Cerrar";
    closeBtn.style.display = "inline-block";
    confirmBtn.style.display = "none";
    dialog.showModal();
}

function showConfirm(titulo, mensaje, textoConfirmar = "Confirmar", textoCancelar = "Cancelar") {
    dialogTitle.innerText = titulo;
    dialogMessage.innerText = mensaje;
    closeBtn.innerText = textoCancelar;
    closeBtn.style.display = "inline-block";
    confirmBtn.innerText = textoConfirmar;
    confirmBtn.style.display = "inline-block";
    dialog.showModal();
    return new Promise((resolve) => { confirmResolver = resolve; });
}

// --- LÓGICA DEL LIBRO ---
async function loadBookDetails(isbn) {
    try {
        const response = await fetch(`../../api/GetBook.php?isbn=${isbn}`);
        const text = await response.text();
        const data = JSON.parse(text);

        if (data.exito) {
            rellenarVista(data.libro);
        } else {
            document.querySelector('.details-info').innerHTML = "<h2>Libro no encontrado</h2>";
        }
    } catch (error) { console.error("Error:", error); }
}

function rellenarVista(libro) {
    document.getElementById('bookTitle').textContent = libro.title || "Título Desconocido";
    document.getElementById('bookAuthor').textContent = (libro.name_author || "") + " " + (libro.last_name || "");
    document.getElementById('bookPrice').textContent = parseFloat(libro.price).toFixed(2) + "€";
    document.getElementById('bookSynopsis').textContent = libro.synopsis || "Sin descripción.";
    document.getElementById('bookISBN').textContent = libro.isbn;
    document.getElementById('bookPages').textContent = libro.pages;
    document.getElementById('bookEditorial').textContent = libro.editorial;

    const img = document.getElementById('bookCover');
    img.src = libro.cover ? `../assets/img/covers/${libro.cover}` : "../assets/img/mood-heart.png";

    const badge = document.getElementById('stockBadge');
    const btnCart = document.getElementById('addToCartBtn');
    const qtyInput = document.getElementById('qtyInput');

    // SI ES ADMIN: Ocultar controles y salir (Esto es fácil de explicar y muy efectivo)
    if (currentUser && currentUser.role === 'admin') {
        btnCart.style.display = 'none';
        qtyInput.style.display = 'none';
        const labelQty = document.querySelector('label[for="qtyInput"]');
        if (labelQty) labelQty.style.display = 'none';
        return;
    }

    if (libro.stock > 0) {
        badge.textContent = "In Stock";
        badge.className = "stock-badge success";
        badge.style.color = "green";
        qtyInput.max = libro.stock;
        btnCart.disabled = false;
        btnCart.textContent = "Comprar Ahora";
    } else {
        badge.textContent = "Out of Stock";
        badge.className = "stock-badge error";
        badge.style.color = "red";
        btnCart.disabled = true;
        btnCart.textContent = "Agotado";
        qtyInput.disabled = true;
    }
    // Configuración del botón de compra
    const newBtn = btnCart.cloneNode(true);
    btnCart.parentNode.replaceChild(newBtn, btnCart);

    newBtn.addEventListener('click', async () => {
        // 1. Verificar Sesión
        if (!currentUser) {
            showModal("Atención", "Debes iniciar sesión para comprar.");
            return;
        }

        // --- NUEVA VALIDACIÓN PREVENTIVA DE STOCK ---
        const cantidad = parseInt(qtyInput.value);
        const stockDisponible = parseInt(libro.stock);

        // Caso A: Cantidad inválida (negativos o texto)
        if (isNaN(cantidad) || cantidad <= 0) {
            showModal("Error", "Por favor introduce una cantidad válida.");
            return;
        }

        // Caso B: Pide más de lo que hay
        if (cantidad > stockDisponible) {
            showModal("Stock Insuficiente", `Solo quedan ${stockDisponible} unidades disponibles.`);
            qtyInput.value = stockDisponible; // Le corregimos el valor automáticamente
            return; // DETENEMOS AQUÍ. No sigue ejecutando.
        }
        // ---------------------------------------------

        // 2. Verificar Tarjeta (Solo si el stock es válido)
        let userCard;
        try {
            const res = await fetch('../../api/GetProfile.php');
            const data = await res.json();
            if (data.success && data.user) {
                const u = data.user;
                userCard = u.card_no || u.CardNo || u.CARD_NO;
            }
        } catch (err) { console.error(err); }

        if (!userCard || userCard.trim() === "") {
            const irPerfil = await showConfirm("Sin tarjeta", "No tienes método de pago. ¿Ir al perfil?", "Ir al perfil", "Cancelar");
            if (irPerfil) window.location.href = "configProfile.html";
            return;
        }

        // 3. Confirmación Final
        if (await showConfirm("Confirmar", `¿Seguro que quieres comprar ${cantidad} unidad(es)?`)) {
            comprarAhora(libro.isbn, cantidad, getUserId(currentUser));
        }
    });
}

// --- COMENTARIOS ---
function handleCommentSection() {
    const container = document.getElementById('userActionContainer');
    const loginPrompt = document.getElementById('loginPrompt');
    const nameDisplay = document.getElementById('userNameDisplay');

    // Lógica simple: Si no es usuario normal, ocultamos cosas
    if (currentUser && currentUser.role !== "admin") {
        container.hidden = false;
        loginPrompt.hidden = true;

        nameDisplay.textContent = currentUser.name || currentUser.user_name || "Usuario";

        const form = document.getElementById('commentForm');
        if (form) form.onsubmit = submitComment;

        const cancelBtn = document.getElementById('cancelEditBtn');
        if (cancelBtn) cancelBtn.onclick = resetForm;

    } else if (currentUser && currentUser.role === "admin") {
        container.hidden = true;
        loginPrompt.hidden = true;
    } else {
        container.hidden = true;
        loginPrompt.hidden = false;
    }
}

async function submitComment(e) {
    e.preventDefault();
    const ratingInput = document.getElementById('ratingScore').value;
    const text = document.getElementById('commentBody').value;
    const msg = document.getElementById('formMessage');
    const isbn = new URLSearchParams(window.location.search).get('isbn');

    if (!text) {
        msg.textContent = "Por favor escribe una reseña.";
        msg.className = "msg-error";
        return;
    }

    const url = isEditing ? '../../api/UpdateComment.php' : '../../api/AddComment.php';

    // --- CORRECCIÓN 100% CLEAN CODE ---
    // Ya no enviamos datos duplicados. Solo lo estándar.
    const payload = {
        profileCode: getUserId(currentUser),
        isbn: isbn,
        text: text,                      // Unificado: Tanto Add como Update esperan 'text'
        rating: parseFloat(ratingInput), // Unificado: Tanto Add como Update esperan 'rating'
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
            msg.textContent = isEditing ? "Actualizado." : "Publicado.";
            resetForm();
            loadComments(isbn);
            setTimeout(() => msg.textContent = "", 3000);
        } else {
            const err = await res.json();
            msg.className = "msg-error";
            msg.textContent = err.message || "Error.";
        }
    } catch (e) { console.error(e); }
}

async function loadComments(isbn) {
    const list = document.getElementById('commentsList');
    const myId = parseInt(getUserId(currentUser));

    try {
        const res = await fetch(`../../api/GetComments.php?isbn=${isbn}`);
        const comments = await res.json();
        list.innerHTML = "";
        let myReview = null;

        // Ordenamos comentarios
        if (myId && comments.length > 0) {
            comments.sort((a, b) => {
                const idA = parseInt(a.profile_code || a.PROFILE_CODE);
                const idB = parseInt(b.profile_code || b.PROFILE_CODE);
                return (idA === myId) ? -1 : (idB === myId) ? 1 : 0;
            });
        }

        if (comments.length > 0) {
            comments.forEach(c => {
                const item = document.createElement('div');
                item.className = 'comment-item';

                const authorId = parseInt(c.profile_code || c.PROFILE_CODE);
                const isMine = myId === authorId;
                const isAdmin = currentUser && currentUser.role === 'admin';

                if (isMine) myReview = c;

                // --- Lógica de botones (Simplificada pero legible) ---
                let btnHtml = "";
                if (isMine || isAdmin) {
                    const safeText = (c.comment_text || "").replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, '\\n');

                    if (isMine) {
                        btnHtml += `<button onclick="startEdit('${safeText}', ${c.valoration})" class="btn-icon">✏️</button>`;
                    }
                    btnHtml += `<button onclick="deleteComment('${isbn}', '${authorId}')" class="btn-icon btn-delete">🗑️</button>`;

                    btnHtml = `<div class="comment-actions">${btnHtml}</div>`;
                }

                item.innerHTML = `
                    ${btnHtml}
                    <div class="comment-header">
                        <strong>${c.user_name || "Usuario"}</strong>
                        <span class="comment-date">${c.dateComent || ""}</span>
                    </div>
                    <div class="star-rating">${getStarHtml(parseFloat(c.valoration))}</div>
                    <p class="comment-text">${c.comment_text || ""}</p>
                `;
                list.appendChild(item);
            });
        } else {
            list.innerHTML = "<p style='text-align:center'>Sin comentarios.</p>";
        }

        // Gestión del formulario si ya comentó
        const container = document.getElementById('userActionContainer');
        const form = document.getElementById('commentForm');
        const msg = document.getElementById('msg-review-exists');
        if (msg) msg.remove();

        if (myReview) {
            if (form) form.style.display = 'none';
            const p = document.createElement('p');
            p.id = 'msg-review-exists';
            p.className = 'info-msg';
            p.style.cssText = "text-align:center; padding:10px; color:#28a745;";
            p.innerText = "Ya has publicado una reseña.";
            container.appendChild(p);
        } else {
            if (form) form.style.display = 'block';
            if (!form && currentUser && currentUser.role !== 'admin') handleCommentSection();
        }

    } catch (e) { console.error(e); }
}

// --- FUNCIONES GLOBALES ---

window.deleteComment = async function (isbn, targetId) {
    if (!await showConfirm("Borrar", "¿Seguro que quieres borrar?")) return;

    try {
        const res = await fetch('../../api/DeleteComment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ isbn: isbn, profileCode: targetId })
        });

        if (res.ok) {
            showModal("Éxito", "Eliminado.");
            if (parseInt(targetId) === parseInt(getUserId(currentUser))) {
                setTimeout(() => location.reload(), 1000);
            } else {
                loadComments(isbn);
            }
        } else {
            showModal("Error", "No se pudo eliminar.");
        }
    } catch (e) { console.error(e); }
};

window.startEdit = function (text, rating) {
    isEditing = true;
    const form = document.getElementById('commentForm');
    const container = document.getElementById('userActionContainer');
    const msg = document.getElementById('msg-review-exists');

    if (!form) return location.reload();

    if (msg) msg.style.display = 'none';
    form.style.display = 'block';
    container.hidden = false;

    document.getElementById('commentBody').value = text;
    document.getElementById('formTitle').innerText = "Editar Reseña";
    document.getElementById('submitBtn').innerText = "Actualizar";
    document.getElementById('cancelEditBtn').style.display = "inline-block";

    setRating(parseFloat(rating));
    container.scrollIntoView({ behavior: 'smooth' });
};

window.setRating = function (val) {
    document.getElementById('ratingScore').value = val;
    const id = 'st' + val.toString().replace('.', '');
    const radio = document.getElementById(id);
    if (radio) radio.checked = true;

    const txt = document.getElementById('rating-text');
    if (txt) txt.innerText = val + "/5";
};

// --- UTILIDADES ---

function resetForm() {
    isEditing = false;
    document.getElementById('commentBody').value = "";
    document.getElementById('formTitle').innerText = "Escribe una reseña";
    document.getElementById('submitBtn').innerText = "Publicar Reseña";
    document.getElementById('cancelEditBtn').style.display = "none";
    document.getElementById('formMessage').innerText = "";
    setRating(5);

    const msg = document.getElementById('msg-review-exists');
    const form = document.getElementById('commentForm');
    if (msg && form) {
        msg.style.display = 'block';
        form.style.display = 'none';
    }
}

async function comprarAhora(isbn, quantity, userId) {
    try {
        const res = await fetch('../../api/BuyNow.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ profileCode: userId, isbn, quantity })
        });

        const text = await res.text();
        let data;
        try { data = JSON.parse(text); }
        catch (e) {
            console.error("Respuesta inválida:", text);
            showModal("Error", "Error inesperado del servidor.");
            return;
        }

        if (data.exito) {
            showModal("¡Compra realizada!", "Gracias por tu pedido.");
        } else {
            showModal("Error", data.error || "Fallo en la compra.");
        }
    } catch (e) {
        console.error(e);
        showModal("Error", "Error de conexión.");
    }
}

export function getUserId(user) {
    return user ? user.profile_code : null;
}

function getStarHtml(rating) {
    let html = '';
    const full = Math.floor(rating);
    const half = (rating % 1) >= 0.5;
    const empty = 5 - full - (half ? 1 : 0);

    for (let i = 0; i < full; i++) html += '<span class="full">★</span>';
    if (half) html += '<span class="half">★</span>';
    for (let i = 0; i < empty; i++) html += '<span>★</span>';
    return html;
}