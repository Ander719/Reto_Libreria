import { checkSession, currentUser } from './session.js';
import { loadHeader, loadFooter } from './header.js';
import { apiFetch } from './apiClient.js';

document.addEventListener('DOMContentLoaded', async () => {

    // Si devuelve true, currentUser ya tendrá datos.
    const isLogged = await checkSession();


    if (!isLogged || currentUser.role !== 'admin') {
        alert("Acceso denegado: Se requieren permisos de administrador.");
        window.location.href = 'login.html';
        return;
    }

    if (!isLogged) {
        window.location.href = 'login.html';
    }
    await loadHeader("opcAdmin");
    await loadFooter();
    fetchBooks();
});

async function fetchBooks() {
    try {
        const data = await apiFetch('../../api/GetAllBooks.php', { credentials: 'include' });
        console.log("Respuesta GetAllBooks:", data);
        const tbody = document.getElementById('booksBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        const books = data.data && data.data.books ? data.data.books : [];

        if (Array.isArray(books)) {
            books.forEach(book => {
                const row = document.createElement('tr');
                const safeCover = (book.cover || "").replace(/&/g, "&amp;").replace(/"/g, "&quot;");
                const safeTitle = (book.title || "").replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
                const safeIsbn = (book.isbn || "").replace(/'/g, "&#39;");
                row.innerHTML = `
                    <td style="text-align:center;">
                        <img src="../assets/img/covers/${safeCover}" class="book-cover-img" width="50" alt="Portada">
                    </td>
                    <td>${safeTitle}</td>
                    <td style="text-align:center;">
                        <button class="view-comments-btn" onclick="verComentarios('${safeIsbn}')">
                            Ver Comentarios
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
    } catch (error) {
        console.error('Error cargando libros:', error);
    }
}

function verComentarios(isbn) {
    window.location.href = `viewComments.html?isbn=${isbn}`;
}
window.verComentarios = verComentarios;