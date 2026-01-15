import { currentUser, checkSession } from './sesion.js';
let isEditing = false;
document.addEventListener("DOMContentLoaded", async () => {
    // 1. Obtener el ISBN de la URL
    // Esto lee lo que hay después del signo ? (ej: ?isbn=1234)
    const params = new URLSearchParams(window.location.search);
    const isbn = params.get('isbn');

    if (!isbn) {
        alert("No se ha especificado un libro.");
        window.location.href = "main.html"; // Volver al inicio si no hay ISBN
        return;
    }

    // 1. Cargar detalles del libro (Tu lógica actual)...
    loadBookDetails(isbn);
    await checkSession();
    // 2. Gestionar la zona de comentarios
    handleCommentSection();

    // 3. Cargar comentarios existentes
    loadComments(isbn);
});

async function loadBookDetails(isbn) {
    try {
        const response = await fetch(`../../api/GetBook.php?isbn=${isbn}`, { method: 'GET' });
        console.log(response)
        let data;
        try {
            data = await response.text();
            data = JSON.parse(data);
        } catch (err) {
            throw new Error('Invalid JSON response: ' + err.message);
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
            alert("Debes iniciar sesión para comprar.");
            window.location.href = "login.html";
            return;
        }

        const cantidad = parseInt(qtyInput.value);

        // Confirmación simple antes de comprar
        if (confirm(`¿Confirmar compra de ${cantidad} ejemplar(es) de "${libro.title}"?`)) {
            await comprarAhora(libro.isbn, cantidad, currentUser.id);
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

    if (currentUser) {
        actionContainer.innerHTML = `
            <h3 id="formTitle">Write a Review</h3>
            <p>Commenting as: <strong>${currentUser.user_name}</strong></p>
            
            <form id="commentForm" class="actions review-form">
                <label for="ratingScore">Rating:</label>
                <select id="ratingScore" class="qty-input rating-select">
                    <option value="5">⭐⭐⭐⭐⭐ - Excellent</option>
                    <option value="4">⭐⭐⭐⭐ - Very Good</option>
                    <option value="3">⭐⭐⭐ - Average</option>
                    <option value="2">⭐⭐ - Poor</option>
                    <option value="1">⭐ - Terrible</option>
                </select>
                
                <label for="commentBody">Your Review:</label>
                <textarea id="commentBody" rows="4" class="review-textarea" placeholder="What did you think about this book?"></textarea>
                
                <div class="btn-container">
                    <button type="submit" id="submitBtn" class="action-btn btn-auto">Submit Review</button>
                    <button type="button" id="cancelEditBtn" class="action-btn btn-cancel btn-auto" style="display:none;">Cancel</button>
                </div>
                <span id="formMessage" class="success-message"></span>
            </form>
        `;

        // Listeners
        document.getElementById('commentForm').addEventListener('submit', (e) => submitComment(e, currentUser));
        document.getElementById('cancelEditBtn').addEventListener('click', () => {
            resetForm();
        });

    } else {
        actionContainer.innerHTML = `
            <div class="login-prompt">
                <p>You must be logged in to post a review.</p>
                <a href="login.html" class="action-btn btn-auto login-link-btn">Log In Now</a>
            </div>
        `;
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

    const profileCode = user.id;
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
    const myProfileCode = currentUser ? currentUser.id : null;
    try {
        const response = await fetch(`../../api/GetComments.php?isbn=${isbn}`);
        const comments = await response.json();

        commentsList.innerHTML = '';
        let myReview = null;

        if (comments.length > 0) {
            comments.forEach(c => {
                const item = document.createElement('div');
                item.classList.add('comment-item');

                const isMine = myProfileCode && (parseInt(c.PROFILE_CODE) === parseInt(myProfileCode));
                if (isMine) myReview = c;

                let buttonsHtml = '';
                if (isMine) {
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
                    <p class="comment-text"></p> <div class="clear-fix"></div>
                `;
                item.querySelector('.comment-text').textContent = c.comment_text;
                commentsList.appendChild(item);
            });
        } else {
            commentsList.innerHTML = '<p class="no-reviews">No reviews yet. Be the first to write one!</p>';
        }

        if (myReview) {
            // 1. Preguntamos al usuario
            const quiereEditar = confirm("Ya has publicado una reseña para este libro. ¿Quieres editarla?");

            if (quiereEditar) {
                // CASO SI: Cargamos el modo edición como antes
                console.log("Usuario acepta editar.");
                startEdit(myReview.comment_text, myReview.valoration, false);
            } else {
                // CASO NO: Ocultamos el formulario de "Escribir reseña"
                // Esto es importante para que no intenten enviar otra y de error de duplicado
                const actionContainer = document.getElementById('userActionContainer');

                // Preparamos el texto seguro por si tiene comillas simples
                const safeText = myReview.comment_text.replace(/'/g, "\\'");

                actionContainer.innerHTML = `
                    <div class="login-prompt" style="background-color: #e8f5e9; border: 1px solid #c8e6c9;">
                        <p style="color: #2e7d32; font-weight: bold;">✅ Ya has valorado este libro.</p>
                        <p>Tu opinión ya está visible para otros usuarios.</p>
                        <button onclick="startEdit('${safeText}', ${myReview.valoration})" class="action-btn btn-auto" style="margin-top:10px;">Editar mi reseña</button>
                    </div>
                `;
            }
        }

    } catch (error) {
        console.error(error);
        commentsList.innerHTML = '<p class="error-msg">Error loading comments.</p>';
    }
}

window.deleteComment = async function (isbn) {
    if (!confirm("Are you sure you want to delete this review?")) return;

    const profileCode = currentUser.id;
    try {
        const response = await fetch('../../api/DeleteComment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ isbn: isbn, profileCode: profileCode })
        });

        if (response.ok) {
            loadComments(isbn);
            resetForm();
        } else {
            alert("Error deleting comment");
        }
    } catch (error) {
        console.error(error);
        alert("Connection error");
    }
};

window.startEdit = function (text, rating, doScroll = true) {
    isEditing = true;
    document.getElementById('commentBody').value = text;
    document.getElementById('ratingScore').value = parseInt(rating);
    document.getElementById('formTitle').innerText = "Edit your Review";
    document.getElementById('submitBtn').innerText = "Update Review";
    document.getElementById('cancelEditBtn').style.display = "inline-block";

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



// --- FUNCIÓN NUEVA PARA COMPRA DIRECTA ---
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

        const data = await response.json();

        if (data.exito) {
            alert("¡Compra realizada con éxito! Gracias por tu pedido.");
            location.reload(); // Recargamos para actualizar el stock visualmente
        } else {
            alert("Error: " + (data.error || "No se pudo completar la compra."));
        }
    } catch (error) {
        console.error(error);
        alert("Error de conexión al procesar la compra.");
    }
}

