import { checkSession, currentUser } from './session.js';
import { loadHeader, loadFooter } from './header.js';
import { apiFetch } from './apiClient.js';

document.addEventListener('DOMContentLoaded', async () => {

    // Esto ejecutará el fetch a PHP. Si devuelve true, currentUser ya tendrá datos.
    const isLogged = await checkSession();

    if (!isLogged || currentUser.role !== 'admin') {
        alert("Acceso denegado: Se requieren permisos de administrador.");
        window.location.href = 'login.html';
        return;
    }

    if (!isLogged) {
        window.location.href = 'login.html';
    }
    await loadHeader("deleteComment");
    await loadFooter();

    const urlParams = new URLSearchParams(window.location.search);
    const isbn = urlParams.get('isbn');

    if (isbn) {
        cargarComentarios(isbn);
    }
});

async function cargarComentarios(isbn) {
    try {
        const data = await apiFetch(`../../api/GetComments.php?isbn=${encodeURIComponent(isbn)}`, { credentials: 'include' });
        console.log("Respuesta GetComments:", data);
        const tbody = document.getElementById('commentsBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        const commentsData = data.data && data.data.comments ? data.data.comments : [];
        const comentarios = Array.isArray(commentsData) ? commentsData : [];

        if (comentarios.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="no-data">No hay comentarios para este libro.</td></tr>';
            return;
        }

        comentarios.forEach(comment => {
            const row = document.createElement('tr');
            const safeName = (comment.user_name || "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
            const safeText = (comment.comment_text || "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
            const safeProfileCode = (comment.profile_code || "").replace(/'/g, "&#39;");
            row.innerHTML = `
                <td class="user-cell">${safeName}</td>
                <td class="text-cell">${safeText}</td>
                <td>
                    <button class="delete-btn" onclick="eliminarComentario('${isbn}', '${safeProfileCode}')">
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
window.eliminarComentario = eliminarComentario;
window.cargarComentarios = cargarComentarios;