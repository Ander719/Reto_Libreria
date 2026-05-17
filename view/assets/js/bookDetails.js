import { currentUser, checkSession } from './session.js';
import { loadHeader, loadFooter } from './header.js';
import { apiFetch } from './apiClient.js';
let isEditing = false;

// CONFIGURACIÓN MODAL
const dialog = document.getElementById('myDialog');
const dialogTitle = document.getElementById('dialogTitle');
const dialogMessage = document.getElementById('dialogMessage');
const closeBtn = document.getElementById('closeDialogBtn');
const confirmBtn = document.getElementById('confirmDialogBtn');

let confirmResolver = null;

document.addEventListener("DOMContentLoaded", async () => {
    await checkSession();
    await loadHeader("bookDetails");

    const params = new URLSearchParams(window.location.search);
    const isbn = params.get('isbn');

    if (!isbn) {
        showModal("Error", "No se ha especificado un libro.");
        setTimeout(() => window.location.href = "main.html", 2000);
        return;
    }
    // Cargar el header
    await loadHeader("bookDetails");
    await loadFooter();

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

// LÓGICA DEL LIBRO
async function loadBookDetails(isbn) {
    try {
        const data = await apiFetch(`../../api/GetBook.php?isbn=${encodeURIComponent(isbn)}`);
        console.log("Status GetBook:", data.code);

        if (data.status === "success" && data.data) {
            rellenarVista(data.data);
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

    // SI ES ADMIN: Ocultar controles y salir
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
        if (!currentUser) {
            showModal("Atención", "Debes iniciar sesión para comprar.");
            return;
        }

        const cantidad = parseInt(qtyInput.value);
        const stockDisponible = parseInt(libro.stock);

        // Caso A: Cantidad inválida
        if (isNaN(cantidad) || cantidad <= 0) {
            showModal("Error", "Por favor introduce una cantidad válida.");
            return;
        }

        // Caso B: Pide más de lo que hay
        if (cantidad > stockDisponible) {
            showModal("Stock Insuficiente", `Solo quedan ${stockDisponible} unidades disponibles.`);
            qtyInput.value = stockDisponible;
            return;
        }

        //Verificar Tarjeta 
        let userCard;
        let direction;
        try {
            const data = await apiFetch('../../api/GetProfile.php', { credentials: 'include' });
            console.log("Status GetProfile:", data.code);
            if (data.status === "success" && data.data && data.data.user) {
                const u = data.data.user;
                userCard = u.card_no || u.CardNo || u.CARD_NO;
                direction = u.direction || u.Direction || u.DIRECTION;
            }
        } catch (err) { console.error(err); }

        const faltaTarjeta = !userCard || String(userCard).trim() === "";
        const faltaDireccion = !direction || String(direction).trim() === "";

        if (faltaTarjeta || faltaDireccion) {
            let msg = "Te faltan datos para comprar:";
            if (faltaTarjeta) msg += "\n- Tarjeta";
            if (faltaDireccion) msg += "\n- Dirección";
            msg += "\n¿Ir al perfil a completarlos?";

            const irPerfil = await showConfirm("Datos incompletos", msg, "Ir al perfil", "Cancelar");
            if (irPerfil) window.location.href = "configProfile.html";
            return;
        }

        // Confirmación
        if (await showConfirm("Confirmar", `¿Seguro que quieres comprar ${cantidad} unidad(es)?`)) {
            comprarAhora(libro.isbn, cantidad, getUserId(currentUser));
        }
    });
}

// COMENTARIOS
function handleCommentSection() {
    const container = document.getElementById('userActionContainer');
    const loginPrompt = document.getElementById('loginPrompt');
    const nameDisplay = document.getElementById('userNameDisplay');

    // Lógica simple: Si es admin ocultamos las cosas de comentarios
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

    const payload = {
        profileCode: getUserId(currentUser),
        isbn: isbn,
        text: text,
        rating: parseFloat(ratingInput),
        date: new Date().toISOString().slice(0, 10)
    };

    try {
        const data = await apiFetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
            credentials: 'include'
        });
        console.log("Status SubmitComment:", data.code);

        msg.className = "msg-success";
        msg.textContent = isEditing ? "Actualizado correctamente." : "Publicado correctamente.";
        resetForm();
        loadComments(isbn);
        setTimeout(() => msg.textContent = "", 3000);

    } catch (e) {
        console.error("Error de red (Fetch failed):", e);
        msg.className = "msg-error";
        msg.textContent = e.message || "Error de conexión. Inténtalo más tarde.";
    }
}
async function loadComments(isbn) {
    const list = document.getElementById('commentsList');
    const myId = parseInt(getUserId(currentUser));

    try {
        const response = await apiFetch(`../../api/GetComments.php?isbn=${encodeURIComponent(isbn)}`);
        console.log("Status GetComments:", response.code);
        const comments = response.status === "success" && Array.isArray(response.data) ? response.data : [];
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

                // Lógica de botones
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

        // Gestión del formulario si el usuario ha comentado
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

//FUNCIONES GLOBALES

window.deleteComment = async function (isbn, targetId) {
    if (!await showConfirm("Borrar", "¿Seguro que quieres borrar?")) return;

    try {
        const data = await apiFetch('../../api/DeleteComment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ isbn: isbn, profileCode: targetId }),
            credentials: 'include'
        });

        console.log("[DELETE] HTTP Status:", data.code);
        showModal("Éxito", data.message || "Eliminado.");

        if (parseInt(targetId) === parseInt(getUserId(currentUser))) {
            setTimeout(() => location.reload(), 1000);
        } else {
            loadComments(isbn);
        }
    } catch (e) {
        console.error(e);
        showModal("Error", e.message || "Error de conexión.");
    }
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

// UTILIDADES

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
        const data = await apiFetch('../../api/BuyNow.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ profileCode: userId, isbn, quantity }),
            credentials: 'include'
        });
        console.log("Status BuyNow:", data.code);

        showModal("¡Compra realizada!", "Gracias por tu pedido.");

        dialog.addEventListener('close', () => {
            location.reload();
        }, { once: true });
    } catch (e) {
        console.error(e);
        showModal("Error", e.message || "Error de conexión.");
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
