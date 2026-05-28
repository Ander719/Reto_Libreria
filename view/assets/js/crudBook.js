import { checkSession } from './session.js';
import { apiFetch } from './apiClient.js';

document.addEventListener('DOMContentLoaded', async () => {
    try {

        // No mostramos el formulario hasta confirmar que es admin.
        const isLogged = await checkSession();
        if (!isLogged) {
            window.location.replace('login.html');
            return;
        }

        const data = await apiFetch('../../api/GetProfile.php', { credentials: 'include' });
        console.log("Respuesta GetProfile (Admin Check):", data);

        if (!data.data || !data.data.user || data.data.role !== 'admin') {
            window.location.replace('main.html');
            return;
        }

        document.body.style.display = 'block';

        initPageLogic();

    } catch (error) {
        console.error("Error de seguridad:", error);
        window.location.replace('login.html');
    }
});

// Prepara el formulario segun mode=create o mode=edit
function initPageLogic() {
    const urlParams = new URLSearchParams(window.location.search);
    const mode = urlParams.get('mode');

    const pageTitle = document.getElementById('pageTitle');
    const searchSection = document.getElementById('searchSection');
    const actionBtn = document.getElementById('actionBtn');
    const isbnInput = document.getElementById('isbn');
    const formInputs = document.querySelectorAll('#bookForm input:not(#isbn), #bookForm textarea');
    const msgSearch = document.getElementById('searchMessage');
    const form = document.getElementById('bookForm');
    const searchSelect = document.getElementById('searchIsbn');


    const reqDialog = document.getElementById('requirementsDialog');
    const closeDialogBtn = document.getElementById('closeDialogBtn');

    const dropZone = document.getElementById("dropZone");
    const inputElement = document.getElementById("coverInput");


    initInterface();


    if (closeDialogBtn && reqDialog) {
        closeDialogBtn.addEventListener('click', () => reqDialog.close());
    }

    // Cambia textos y campos segun si se crea o se edita
    function initInterface() {
        if (mode === 'create') {
            if (pageTitle) pageTitle.innerText = "Añadir Nuevo Libro";
            if (searchSection) searchSection.style.display = "none";
            if (actionBtn) actionBtn.innerText = "Crear Libro";
            if (isbnInput) isbnInput.readOnly = false;
        } else if (mode === 'edit') {
            if (pageTitle) pageTitle.innerText = "Editar Libro";
            if (searchSection) searchSection.style.display = "block";
            if (actionBtn) actionBtn.innerText = "Guardar Cambios";
            if (isbnInput) isbnInput.readOnly = true;
            toggleForm(true);
            loadBooksToSelect();
        }
    }

    // Llena el selector de libros del modo edicion
    async function loadBooksToSelect() {
        try {
            const data = await apiFetch('../../api/GetAllBooks.php');
            console.log("Respuesta GetAllBooks (Select list):", data);
            const books = data.data || [];

            if (searchSelect) {
                searchSelect.innerHTML = '<option value="">-- Seleccione un libro --</option>';
                if (Array.isArray(books)) {
                    books.forEach(book => {
                        const option = document.createElement('option');
                        option.value = book.isbn;
                        option.textContent = `${book.title} (${book.isbn})`;
                        searchSelect.appendChild(option);
                    });
                }
            }
        } catch (err) { console.error(err); }
    }

    const btnSearch = document.getElementById('btnSearch');
    if (btnSearch) {
        btnSearch.addEventListener('click', async (e) => {
            e.preventDefault();
            const isbnToSearch = searchSelect.value;
            if (!isbnToSearch) {
                if (msgSearch) msgSearch.innerText = "Por favor, seleccione un libro.";
                return;
            }

            try {
                const data = await apiFetch(`../../api/GetBook.php?isbn=${encodeURIComponent(isbnToSearch)}`);
                console.log("Respuesta GetBook (Search):", data);
                fillForm(data.data);
                if (msgSearch) msgSearch.innerText = "";
                toggleForm(false);
            } catch (error) {
                console.error(error);
                if (msgSearch) msgSearch.innerText = error.message || "Error al cargar datos.";
            }
        });
    }

    if (form) {
        // Antes de enviar se revisan ISBN e imagen; luego se elige alta o modificacion.
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const isbnVal = isbnInput.value.trim();

            const isNumeric = /^\d+$/.test(isbnVal);

            if (isbnVal.length !== 13 || !isNumeric) {
                if (reqDialog) {
                    reqDialog.showModal();
                } else {
                    alert("El ISBN debe tener exactamente 13 dígitos numéricos.");
                }
                return;
            }

            const selectedFile = inputElement && inputElement.files ? inputElement.files[0] : null;
            if (selectedFile) {
                const maxSize = 2 * 1024 * 1024;
                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

                if (selectedFile.size >= maxSize) {
                    alert('La imagen supera el tamaño máximo permitido (2MB).');
                    return;
                }

                if (!allowedTypes.includes(selectedFile.type)) {
                    alert('Formato de imagen no válido. Solo JPG, PNG o WEBP.');
                    return;
                }
            }

            const formData = new FormData(form);
            const url = mode === 'create' ? '../../api/AddBook.php' : '../../api/ModifyBook.php';

            try {
                const data = await apiFetch(url, { method: 'POST', body: formData, credentials: 'include' });
                console.log(`Respuesta ${mode === 'create' ? 'AddBook' : 'ModifyBook'}:`, data);
                alert(data.message || "Operación exitosa");
                window.location.href = 'bookOptions.html';
            } catch (error) {
                console.error(error);
                alert("Error: " + (error.message || "No se pudo completar la operación."));
            }
        });
    }

    // Bloquea el formulario hasta elegir libro en modo edicion
    function toggleForm(isDisabled) {
        formInputs.forEach(input => input.disabled = isDisabled);
        if (actionBtn) actionBtn.disabled = isDisabled;
    }

    // Copia los datos del libro recibido al formulario
    function fillForm(data) {
        const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ""; };

        setVal('isbn', data.isbn);
        setVal('title', data.title);
        setVal('pages', data.pages);
        setVal('stock', data.stock);
        setVal('price', data.price);
        setVal('editorial', data.editorial);
        setVal('synopsis', data.synopsis);
        setVal('authorName', data.name_author);
        setVal('authorSurname', data.last_name);

        const coverName = data.cover;
        setVal('cover', coverName);

        if (coverName && dropZone) {
            updateThumbnailVisual(dropZone, `../assets/img/covers/${coverName}`, coverName);
        } else if (dropZone) {
            resetDropZone();
        }
    }

    // Quita la miniatura de portada
    function resetDropZone() {
        const prompt = dropZone.querySelector(".drop-zone__prompt");
        const thumb = dropZone.querySelector(".drop-zone__thumb");
        if (thumb) thumb.remove();
        if (prompt) prompt.style.display = 'block';
    }

    if (dropZone && inputElement) {
        // El drop actualiza el input file y la miniatura a la vez.
        dropZone.addEventListener("click", () => inputElement.click());
        inputElement.addEventListener("change", () => {
            if (inputElement.files.length) updateThumbnailFile(dropZone, inputElement.files[0]);
        });
        dropZone.addEventListener("dragover", (e) => { e.preventDefault(); dropZone.classList.add("drop-zone--over"); });
        dropZone.addEventListener("dragleave", (e) => { if (!dropZone.contains(e.relatedTarget)) dropZone.classList.remove("drop-zone--over"); });
        dropZone.addEventListener("dragend", () => dropZone.classList.remove("drop-zone--over"));
        dropZone.addEventListener("drop", (e) => {
            e.preventDefault();
            dropZone.classList.remove("drop-zone--over");
            if (e.dataTransfer.files.length) {
                inputElement.files = e.dataTransfer.files;
                updateThumbnailFile(dropZone, e.dataTransfer.files[0]);
            }
        });
    }

    // Lee una imagen local para mostrar miniatura
    function updateThumbnailFile(dropZoneElement, file) {
        if (file.type.startsWith("image/")) {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = () => updateThumbnailVisual(dropZoneElement, reader.result, file.name);
        }
    }

    // Pinta la miniatura desde una URL o desde un archivo local
    function updateThumbnailVisual(dropZoneElement, url, label) {
        let thumbnailElement = dropZoneElement.querySelector(".drop-zone__thumb");
        const prompt = dropZoneElement.querySelector(".drop-zone__prompt");
        if (prompt) prompt.style.display = "none";
        if (!thumbnailElement) {
            thumbnailElement = document.createElement("div");
            thumbnailElement.classList.add("drop-zone__thumb");
            dropZoneElement.appendChild(thumbnailElement);
        }
        thumbnailElement.dataset.label = label;
        thumbnailElement.style.backgroundImage = `url('${url}')`;
    }
}
