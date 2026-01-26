// view/assets/js/listBooksForComments.js

document.addEventListener('DOMContentLoaded', () => {
    fetchBooks();
});

function fetchBooks() {
    // La ruta debe subir dos niveles para llegar a la carpeta 'api'
    fetch('../../api/GetAllBooks.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('booksBody');
            if (!tbody) return; // Seguridad si el elemento no existe

            tbody.innerHTML = '';

            if (data.success && data.books) {
                data.books.forEach(book => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td style="text-align:center;">
                            <img src="../assets/img/covers/${book.cover}" width="50">
                        </td>
                        <td>${book.title}</td>
                        <td>${book.isbn}</td>
                        <td>
                            <button onclick="verComentarios('${book.isbn}')">Ver Comentarios</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            }
        })
        .catch(error => console.error('Error cargando libros:', error));
}

function verComentarios(isbn) {
    // Asegúrate de que el nombre del archivo sea viewComments.html
    window.location.href = `viewComments.html?isbn=${isbn}`;
}