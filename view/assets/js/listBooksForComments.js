import { checkSession, currentUser } from './session.js';
import { loadHeader, loadFooter } from './header.js';

document.addEventListener('DOMContentLoaded', async () => {

    // Si devuelve true, currentUser ya tendrá datos.
    const isLogged = await checkSession();


    if (!isLogged || currentUser.role !== 'admin') {
        alert("Acceso denegado: Se requieren permisos de administrador.");
        window.location.href = 'main.html';
        return;
    }

    if (!isLogged) {
        window.location.href = 'login.html';
    }
    await loadHeader("opcAdmin");
    await loadFooter();
    fetchBooks();
});

function fetchBooks() {
    fetch('../../api/GetAllBooks.php')
        .then(async response => {
            console.log("Status Code HTTP:", response.status);
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById('booksBody');
            if (!tbody) return;

            tbody.innerHTML = '';

            if (data.success && data.books) {
                data.books.forEach(book => {
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

        })
        .catch(error => console.error('Error cargando libros:', error));
}

function verComentarios(isbn) {
    window.location.href = `viewComments.html?isbn=${isbn}`;
}
window.verComentarios = verComentarios;