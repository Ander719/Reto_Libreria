import { checkSession, currentUser } from './session.js';
import { loadHeader, loadFooter } from './header.js';
import { apiFetch } from './apiClient.js';

document.addEventListener('DOMContentLoaded', async () => {

    // Moderacion de comentarios: solo admins.
    const isLogged = await checkSession();

    if (!isLogged) {
        window.location.replace('login.html');
        return;
    }

    if (currentUser.role !== 'admin') {
        window.location.replace('main.html');
        return;
    }
    await loadHeader("deleteComment");
    await loadFooter();

    const urlParams = new URLSearchParams(window.location.search);
    const isbn = urlParams.get('isbn');

    if (isbn) {
        cargarComentarios(isbn);
    }
});

/**
 * Carga comentarios y los pinta en la tabla.
 *
 * @param {string} isbn ISBN del libro seleccionado.
 * @returns {Promise<void>}
 */
async function cargarComentarios(isbn) {
    try {
        const data = await apiFetch(`../../api/GetComments.php?isbn=${encodeURIComponent(isbn)}`);
        console.log("Respuesta GetComments:", data);
        const tbody = document.getElementById('commentsBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        const comentarios = Array.isArray(data.data) ? data.data : [];

        if (comentarios.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="no-data">No hay comentarios para este libro.</td></tr>';
            return;
        }

        comentarios.forEach(comment => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="user-cell">${comment.user_name}</td>
                <td class="text-cell">${comment.comment_text}</td>
                <td>
                    <button class="delete-btn" onclick="eliminarComentario('${isbn}', '${comment.profile_code}')">
                        Eliminar
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    } catch (error) {
        console.error('Error al obtener comentarios:', error);
    }
}

/**
 * Borra un comentario despues de pedir confirmacion.
 *
 * @param {string} isbn ISBN comentado.
 * @param {number|string} profileCode Perfil propietario del comentario.
 * @returns {Promise<void>}
 */
async function eliminarComentario(isbn, profileCode) {
    if (confirm('¿Estás seguro de que deseas eliminar este comentario?')) {
        try {
            const result = await apiFetch('../../api/DeleteComment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    isbn: isbn,
                    profileCode: profileCode
                }),
                credentials: 'include'
            });
            console.log("Respuesta DeleteComments:", result);
            alert(result.message);
            cargarComentarios(isbn);
        } catch (error) {
            console.error('Error al eliminar:', error);
            alert(error.message || 'No se pudo eliminar el comentario.');
        }
    }
}

// Las filas usan onclick inline, por eso se expone en window.
window.eliminarComentario = eliminarComentario;
window.cargarComentarios = cargarComentarios;
