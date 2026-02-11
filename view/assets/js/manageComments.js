import { checkSession, currentUser } from './session.js';
import { loadHeader, loadFooter} from './header.js';

document.addEventListener('DOMContentLoaded', async() => {
    console.log("Verificando sesión con el servidor...");

    // Esto ejecutará el fetch a PHP. Si devuelve true, currentUser ya tendrá datos.
    const isLogged = await checkSession();

    if (isLogged) {
        console.log("Usuario logueado:", currentUser);
    } else {
        console.log("No hay sesión activa, redirigiendo...");
        window.location.href = 'login.html';
    }
    await loadHeader("deleteComment");
    await loadFooter();

    const urlParams = new URLSearchParams(window.location.search);
    const isbn = urlParams.get('isbn');

    if (isbn) {
        cargarComentarios(isbn);
    } else {
        console.error("No se proporcionó un ISBN en la URL");
    }
});

function cargarComentarios(isbn) {
    fetch(`../../api/GetComments.php?isbn=${isbn}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('commentsBody');
            if (!tbody) return;
            
            tbody.innerHTML = '';

            // El API devuelve un array directamente
            const comentarios = data;

            if (!comentarios || comentarios.length === 0) {
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
        })
        .catch(error => console.error('Error al obtener comentarios:', error));
}

function eliminarComentario(isbn, profileCode) {
    if (confirm('¿Estás seguro de que deseas eliminar este comentario?')) {
        fetch('../../api/DeleteComment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                isbn: isbn, 
                profileCode: profileCode 
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message) });
            }
            return response.json();
        })
        .then(result => {
            if (result.success || result.message.includes("correctamente")) {
                alert(result.message); 
                cargarComentarios(isbn); 
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error al eliminar:', error);
            alert('No se pudo eliminar el comentario.');
        });
    }
}
window.eliminarComentario = eliminarComentario;
window.cargarComentarios = cargarComentarios;