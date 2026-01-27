// view/assets/js/listBooksForComments.js

document.addEventListener('DOMContentLoaded', () => {
    fetchBooks();
});

function fetchBooks() {
    // La ruta sube dos niveles para llegar a la carpeta 'api' desde view/html/
    fetch('../../api/GetAllBooks.php')
        .then(response => response.json())
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
    // Redirige a la página de visualización pasando el ISBN por la URL
    window.location.href = `viewComments.html?isbn=${isbn}`;
}