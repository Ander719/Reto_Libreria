import { checkSession, currentUser } from './session.js';
import { loadHeader, loadFooter } from './header.js';
import { apiFetch } from './apiClient.js';

document.addEventListener('DOMContentLoaded', async () => {

    // Esta pantalla es solo para admins.
    const isLogged = await checkSession();


    if (!isLogged) {
        window.location.replace('login.html');
        return;
    }

    if (currentUser.role !== 'admin') {
        window.location.replace('main.html');
        return;
    }
    await loadHeader("opcAdmin");
    await loadFooter();
    fetchBooks();
});

// Lista libros para elegir cuales comentarios revisar
async function fetchBooks() {
    try {
        const data = await apiFetch('../../api/GetAllBooks.php');
        console.log("Respuesta GetAllBooks:", data);
        const tbody = document.getElementById('booksBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (Array.isArray(data.data)) {
            data.data.forEach(book => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td style="text-align:center;">
                        <img src="../assets/img/covers/${book.cover}" class="book-cover-img" width="50" alt="Portada">
                    </td>
                    <td>${book.title}</td>
                    <td style="text-align:center;">
                        <button class="view-comments-btn" onclick="verComentarios('${book.isbn}')">
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

// Abre la vista de comentarios del libro elegido
function verComentarios(isbn) {
    window.location.href = `viewComments.html?isbn=${isbn}`;
}

// Las filas usan onclick inline, por eso se expone en window.
window.verComentarios = verComentarios;
