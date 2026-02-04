// view/assets/js/crudBook.js
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const mode = urlParams.get('mode');

    // Referencias al DOM
    const pageTitle = document.getElementById('pageTitle');
    const searchSection = document.getElementById('searchSection');
    const actionBtn = document.getElementById('actionBtn');
    const isbnInput = document.getElementById('isbn');
    const formInputs = document.querySelectorAll('#bookForm input:not(#isbn), #bookForm textarea');
    const msgSearch = document.getElementById('searchMessage');
    const form = document.getElementById('bookForm');
    const searchSelect = document.getElementById('searchIsbn'); 

    // DropZone Refs
    const dropZone = document.getElementById("dropZone");
    const inputElement = document.getElementById("coverInput");

    initInterface();

    function initInterface() {
        if (mode === 'create') {
            pageTitle.innerText = "Añadir Nuevo Libro";
            searchSection.style.display = "none"; 
            actionBtn.innerText = "Crear Libro";
            isbnInput.readOnly = false;
        } else if (mode === 'edit') {
            pageTitle.innerText = "Editar Libro";
            searchSection.style.display = "block";
            actionBtn.innerText = "Guardar Cambios";
            isbnInput.readOnly = true; 
            toggleForm(true); 
            loadBooksToSelect(); 
        }
    }

    async function loadBooksToSelect() {
    try {
        const res = await fetch('../../api/GetAllBooks.php');
        const books = await res.json();
        
        // Limpiar el select antes de rellenar
        const searchSelect = document.getElementById('searchIsbn'); 
        searchSelect.innerHTML = '<option value="">-- Seleccione un libro --</option>';

        // Validar que 'books' sea un array
        if (Array.isArray(books)) {
            books.forEach(book => {
                const option = document.createElement('option');
                // Asegúrate de que estos nombres coincidan con tu BD (minúsculas/mayúsculas)
                option.value = book.isbn || book.ISBN; 
                option.textContent = `${book.title || book.TITLE} (${book.isbn || book.ISBN})`;
                searchSelect.appendChild(option);
            });
        } else {
            console.error("La API no devolvió un array:", books);
        }
    } catch (err) {
        console.error("Error cargando la lista de libros:", err);
    }
}

    const btnSearch = document.getElementById('btnSearch');
    if (btnSearch) {
        btnSearch.addEventListener('click', (e) => {
            e.preventDefault();
            const isbnToSearch = searchSelect.value;
            if (!isbnToSearch) {
                msgSearch.innerText = "Por favor, seleccione un libro.";
                return;
            }
            fetch(`../../api/GetBook.php?isbn=${isbnToSearch}`)
                .then(res => res.json())
                .then(data => {
                    if (data.exito && data.libro) {
                        fillForm(data.libro); 
                        msgSearch.innerText = "";
                        toggleForm(false);
                    } else {
                        msgSearch.innerText = "Error al cargar datos.";
                    }
                });
        });
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        
        // RESTRICCIÓN: ISBN 13 caracteres
        const isbnVal = isbnInput.value.trim();
        if (isbnVal.length !== 13) {
            alert("El ISBN debe tener exactamente 13 caracteres.");
            return;
        }

        const formData = new FormData(form);
        const url = mode === 'create' ? '../../api/AddBook.php' : '../../api/ModifyBook.php';

        fetch(url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.exito || data.success) {
                    alert(data.message || "Operación exitosa");
                    window.location.href = 'bookOptions.html'; 
                } else {
                    alert("Error: " + data.error);
                }
            })
            .catch(() => alert("Error de conexión con el servidor."));
    });

    function toggleForm(isDisabled) {
        formInputs.forEach(input => input.disabled = isDisabled);
        actionBtn.disabled = isDisabled;
    }

    function fillForm(data) {
        document.getElementById('isbn').value = data.isbn || "";
        document.getElementById('title').value = data.title || "";
        document.getElementById('pages').value = data.pages || "";
        document.getElementById('stock').value = data.stock || "";
        document.getElementById('price').value = data.price || "";
        document.getElementById('editorial').value = data.editorial || "";
        document.getElementById('synopsis').value = data.synopsis || "";
        document.getElementById('authorName').value = data.name_author || ""; 
        document.getElementById('authorSurname').value = data.last_name || ""; 
        document.getElementById('cover').value = data.cover || ""; 
    }
    function resetDropZone() {
        const prompt = dropZone.querySelector(".drop-zone__prompt");
        const thumb = dropZone.querySelector(".drop-zone__thumb");
        if (thumb) thumb.remove();
        if (prompt) prompt.style.display = 'block';
    }

    /* ------------------------------------------------------
       LÓGICA DRAG & DROP
       ------------------------------------------------------ */
    dropZone.addEventListener("click", () => inputElement.click());

    inputElement.addEventListener("change", () => {
        if (inputElement.files.length) {
            updateThumbnailFile(dropZone, inputElement.files[0]);
        }
    });

    dropZone.addEventListener("dragover", (e) => {
        e.preventDefault();
        dropZone.classList.add("drop-zone--over");
    });

    dropZone.addEventListener("dragleave", (e) => {
        if (!dropZone.contains(e.relatedTarget)) dropZone.classList.remove("drop-zone--over");
    });
    
    dropZone.addEventListener("dragend", () => dropZone.classList.remove("drop-zone--over"));

    dropZone.addEventListener("drop", (e) => {
        e.preventDefault();
        dropZone.classList.remove("drop-zone--over");
        if (e.dataTransfer.files.length) {
            inputElement.files = e.dataTransfer.files;
            updateThumbnailFile(dropZone, e.dataTransfer.files[0]);
        }
    });

    function updateThumbnailFile(dropZoneElement, file) {
    }

    function updateThumbnailVisual(dropZoneElement, url, label) {
    }
});