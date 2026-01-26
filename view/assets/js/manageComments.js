document.addEventListener('DOMContentLoaded', () => {
    // Obtener el ISBN de la URL
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

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3">No hay comentarios para este libro.</td></tr>';
                return;
            }

            data.forEach(comment => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${comment.user_name}</td>
                    <td>${comment.comment_text}</td>
                    <td>
                        <button onclick="eliminarComentario('${isbn}', '${comment.profile_code}')">Eliminar</button>
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
            body: JSON.stringify({ isbn: isbn, profileCode: profileCode })
        })
        .then(response => response.json())
        .then(result => {
            alert(result.message);
            location.reload(); // Recargar para ver los cambios
        })
        .catch(error => console.error('Error al eliminar:', error));
    }
}