// Simulación: En un caso real, esto vendría de tu login.js al hacer el inicio de sesión.
// localStorage.setItem('currentUser', JSON.stringify({ username: 'JuanP', profileCode: 1 }));

document.addEventListener("DOMContentLoaded", () => {
    // 1. Cargar detalles del libro (Tu lógica actual)...
    // ... loadBookDetails();

    // 2. Gestionar la zona de comentarios
    handleCommentSection();
});

function handleCommentSection() {
    const actionContainer = document.getElementById('userActionContainer');

    // RECUPERAR USUARIO (Esto es clave para la rúbrica de Seguridad IL8.4)
    const currentUser = JSON.parse(localStorage.getItem('currentUser'));

    if (currentUser) {
        // A) USUARIO LOGUEADO -> MOSTRAR FORMULARIO
        actionContainer.innerHTML = `
            <h3>Write a Review</h3>
            <p>Commenting as: <strong>${currentUser.username}</strong></p>
            <form id="commentForm" class="actions" style="background: white; border: none; padding: 0; box-shadow: none;">
                <label for="ratingScore">Rating:</label>
                <select id="ratingScore" class="qty-input" style="width: 150px; margin-bottom: 10px;">
                    <option value="5">⭐⭐⭐⭐⭐ - Excellent</option>
                    <option value="4">⭐⭐⭐⭐ - Very Good</option>
                    <option value="3">⭐⭐⭐ - Average</option>
                    <option value="2">⭐⭐ - Poor</option>
                    <option value="1">⭐ - Terrible</option>
                </select>
                
                <label for="commentBody">Your Review:</label>
                <textarea id="commentBody" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" placeholder="What did you think about this book?"></textarea>
                
                <button type="submit" class="action-btn" style="margin-top: 10px; width: auto;">Submit Review</button>
                <span id="formMessage" style="color: green; display: none; margin-top: 10px; font-weight: bold;"></span>
            </form>
        `;

        // Añadir el Listener para enviar el comentario
        document.getElementById('commentForm').addEventListener('submit', (e) => submitComment(e, currentUser));

    } else {
        // B) NO LOGUEADO -> MOSTRAR ENLACE AL LOGIN
        actionContainer.innerHTML = `
            <div style="text-align: center; padding: 20px; background-color: #f9f9f9; border-radius: 8px;">
                <p>You must be logged in to post a review.</p>
                <a href="login.html" class="action-btn" style="display: inline-block; width: auto; text-decoration: none; margin-top: 10px;">Log In Now</a>
            </div>
        `;
    }
}

async function submitComment(e, user) {
    e.preventDefault();

    // Obtener datos del formulario
    const rating = document.getElementById('ratingScore').value;
    const text = document.getElementById('commentBody').value;
    const messageSpan = document.getElementById('formMessage');

    // Obtener el ISBN de la URL (asumiendo ?isbn=123)
    const urlParams = new URLSearchParams(window.location.search);
    const isbn = urlParams.get('isbn');

    // Validaciones básicas antes de enviar
    if (!text) {
        alert("Please write a comment!");
        return;
    }
    if (!isbn) {
        alert("Error: No book loaded (ISBN missing)");
        return;
    }

    // OBJETO DE DATOS PARA ENVIAR AL SERVIDOR (PHP)
    const commentData = {
        profileCode: user.profileCode, // ID del usuario logueado
        isbn: isbn,
        comment: text,
        valoration: rating,
        date: new Date().toISOString().slice(0, 10) // Fecha actual YYYY-MM-DD
    };

    console.log("Enviando al servidor:", commentData);

    try {
        // PETICIÓN REAL AL PHP
        const response = await fetch('../api/AddComment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(commentData)
        });

        // Convertimos la respuesta del servidor a JSON
        const result = await response.json();

        if (response.ok) {
            // ÉXITO (HTTP 200-299)
            messageSpan.innerText = result.message || "Review submitted successfully!";
            messageSpan.style.color = "green";
            messageSpan.style.display = "block";

            // Limpiar campo
            document.getElementById('commentBody').value = "";

            // Opcional: Recargar los comentarios para ver el nuevo
            // loadComments(isbn); 

        } else {
            // ERROR DEL SERVIDOR (HTTP 400, 500, etc.)
            messageSpan.innerText = "Error: " + (result.message || "Could not save review.");
            messageSpan.style.color = "red";
            messageSpan.style.display = "block";
        }

    } catch (error) {
        // ERROR DE RED O DE CONEXIÓN
        console.error("Error en la petición:", error);
        messageSpan.innerText = "Network Error. Please try again later.";
        messageSpan.style.color = "red";
        messageSpan.style.display = "block";
    }
}