document.addEventListener('DOMContentLoaded', () => {
    // Obtener el modo de la URL (create, edit, delete)
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

    // Inicializar la interfaz según el modo
    initInterface();

    function initInterface() {
        if (mode === 'create') {
            pageTitle.innerText = "Añadir Nuevo Libro";
            searchSection.style.display = "none"; // Ocultar buscador en create
            actionBtn.innerText = "Crear Libro";
            isbnInput.readOnly = false;
        } else if (mode === 'edit') {
            pageTitle.innerText = "Editar Libro";
            searchSection.style.display = "block";
            actionBtn.innerText = "Guardar Cambios";
            isbnInput.readOnly = true; // El ISBN no se edita, es la clave
            toggleForm(true); // Bloquear hasta que se busque
        }
    }
    // --- LÓGICA DE BÚSQUEDA (CORREGIDA) ---
    document.getElementById('btnSearch').addEventListener('click', (e) => {
        e.preventDefault();
        const isbnToSearch = document.getElementById('searchIsbn').value;
        if (!isbnToSearch) return;

        fetch(`../../api/GetBook.php?isbn=${isbnToSearch}`)
            .then(res => {
                if (!res.ok) throw new Error("Error conexión servidor");
                return res.json();
            })
            .then(data => {
                // CORRECCIÓN AQUÍ: Usamos data.libro en lugar de data.resultado
                if (data.exito && data.libro) {
                    fillForm(data.libro); 
                    msgSearch.innerText = "";

                    if (mode === 'edit') toggleForm(false);
                    else if (mode === 'delete') actionBtn.disabled = false;
                } else {
                    msgSearch.innerText = "Libro no encontrado.";
                    form.reset();
                    document.getElementById('searchIsbn').value = isbnToSearch;
                    if (mode !== 'create') toggleForm(true);
                }
            })
            .catch(err => {
                console.error(err);
                alert("Error al buscar el libro (Revisa consola para detalles).");
            });
    });

    // --- LÓGICA DE ENVÍO DEL FORMULARIO ---
    form.addEventListener('submit', (e) => {
        e.preventDefault();

        // 1. Validaciones básicas
        if (!mode) {
            alert("Error: Modo no definido en la URL (?mode=...)");
            return;
        }
        if (mode === 'delete' && !confirm("¿Seguro que deseas eliminar este libro permanentemente?")) {
            return;
        }

        // 2. Preparar la URL de destino
        let url = '';
        if (mode === 'create') url = '../../api/AddBook.php';
        else if (mode === 'edit') url = '../../api/ModifyBook.php';
        else if (mode === 'delete') url = `../../api/DeleteBook.php?isbn=${document.getElementById('isbn').value}`; // Delete suele ir por GET/URL o JSON body

        // 3. SELECCIÓN DE MÉTODO DE ENVÍO
        
        // CASO A: CREAR (Usa FormData para enviar Archivos + Datos)
        if (mode === 'create') {
            const formData = new FormData(e.target);
            // formData ya contiene 'coverFile', 'authorName', 'authorSurname', etc.

            fetch(url, {
                method: 'POST',
                body: formData // El navegador pone automáticamente el Content-Type multipart/form-data
            })
            .then(res => res.json())
            .then(handleResponse)
            .catch(handleError);
        } 
        
        // CASO B: EDITAR (Usa JSON, compatible con tu ModifyBook.php actual)
        else if (mode === 'edit') {
            // Convertimos el form a objeto simple
            const formData = new FormData(e.target);
            const dataObj = Object.fromEntries(formData.entries());
            
            // IMPORTANTE: ModifyBook.php espera JSON raw
            fetch(url, {
                method: 'POST', // O PUT si tu API lo soporta
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataObj)
            })
            .then(res => res.json())
            .then(handleResponse)
            .catch(handleError);
        }
    });

    // Manejador de respuesta común
    function handleResponse(data) {
        if (data.exito || data.success) { // A veces usas 'exito', a veces 'success'
            alert(data.message || "Operación exitosa");
            window.location.href = 'bookOptions.html';
        } else {
            alert("Error del servidor: " + (data.error || "Desconocido"));
        }
    }

    // Manejador de errores de red
    function handleError(err) {
        console.error(err);
        alert("Error de conexión con la API.");
    }

    // Utilidad: Bloquear/Desbloquear inputs
    function toggleForm(isDisabled) {
        formInputs.forEach(input => input.disabled = isDisabled);
        if (mode !== 'create') actionBtn.disabled = isDisabled;
    }

    // Utilidad: Rellenar formulario con datos de la BD
    f// Utilidad: Rellenar formulario con datos de la BD
    function fillForm(data) {
        // 1. Rellenar campos de texto
        document.getElementById('isbn').value = data.isbn || "";
        document.getElementById('title').value = data.title || "";
        document.getElementById('pages').value = data.pages || "";
        document.getElementById('stock').value = data.stock || "";
        document.getElementById('price').value = data.price || "";
        document.getElementById('editorial').value = data.editorial || "";
        document.getElementById('synopsis').value = data.synopsis || "";
        
        // 2. Rellenar ID Autor (Asegúrate de tener este input en tu HTML)
        // Si tu input se llama 'authorName' o 'id_author', ajusta el ID aquí abajo:
        if(document.getElementById('author')) {
             document.getElementById('author').value = data.id_author || ""; 
        }

        // 3. LOGICA PARA MOSTRAR LA PORTADA (COVER)
        const coverName = data.cover; // Nombre del archivo (ej: cover_123.jpg)
        
        // Input hidden (para enviar el nombre antiguo si no se cambia)
        document.getElementById('cover').value = coverName || "";

        // Actualizar visualmente la zona de carga (DropZone)
        const dropZone = document.getElementById("dropZone");
        
        if (dropZone && coverName) {
            // A. Quitar el texto de "Arrastra tu archivo aquí" si existe
            const prompt = dropZone.querySelector(".drop-zone__prompt");
            if (prompt) prompt.remove();

            // B. Buscar o crear el elemento del thumbnail
            let thumbnailElement = dropZone.querySelector(".drop-zone__thumb");
            if (!thumbnailElement) {
                thumbnailElement = document.createElement("div");
                thumbnailElement.classList.add("drop-zone__thumb");
                dropZone.appendChild(thumbnailElement);
            }

            // C. Establecer la imagen de fondo
            // IMPORTANTE: La ruta es relativa al archivo HTML (crudBook.html), no al JS.
            // Si crudBook.html está en 'view/html/' y las imágenes en 'view/assets/img/covers/'
            thumbnailElement.style.backgroundImage = `url('../assets/img/covers/${coverName}')`;
            
            // D. Mostrar el nombre del archivo al pasar el ratón
            thumbnailElement.dataset.label = coverName;
        }
    }

    /* ------------------------------------------------------
       LÓGICA DRAG & DROP PARA LA PORTADA
       ------------------------------------------------------ */
    const dropZone = document.getElementById("dropZone");
    const inputElement = document.getElementById("coverInput");
    const hiddenInput = document.getElementById("cover"); // Para Edit mode (mantener valor antiguo)

    // Clic en la zona abre el selector
    dropZone.addEventListener("click", () => inputElement.click());

    // Cambio en el input (selección manual)
    inputElement.addEventListener("change", () => {
        if (inputElement.files.length) {
            updateThumbnail(dropZone, inputElement.files[0]);
        }
    });

    // Arrastre sobre la zona
    dropZone.addEventListener("dragover", (e) => {
        e.preventDefault();
        dropZone.classList.add("drop-zone--over");
    });

    // Salir de la zona
    ["dragleave", "dragend"].forEach((type) => {
        dropZone.addEventListener(type, () => dropZone.classList.remove("drop-zone--over"));
    });

    // Soltar archivo
    dropZone.addEventListener("drop", (e) => {
        e.preventDefault();
        dropZone.classList.remove("drop-zone--over");

        if (e.dataTransfer.files.length) {
            // Asignar los archivos al input real para que se envíen con FormData
            inputElement.files = e.dataTransfer.files;
            updateThumbnail(dropZone, e.dataTransfer.files[0]);
        }
    });

    function updateThumbnail(dropZoneElement, file) {
        let thumbnailElement = dropZoneElement.querySelector(".drop-zone__thumb");

        // Quitar texto de ayuda
        if (dropZoneElement.querySelector(".drop-zone__prompt")) {
            dropZoneElement.querySelector(".drop-zone__prompt").remove();
        }

        // Crear elemento si no existe
        if (!thumbnailElement) {
            thumbnailElement = document.createElement("div");
            thumbnailElement.classList.add("drop-zone__thumb");
            dropZoneElement.appendChild(thumbnailElement);
        }

        // Mostrar nombre del archivo
        thumbnailElement.dataset.label = file.name;

        // Leer imagen para previsualización
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => {
            thumbnailElement.style.backgroundImage = `url('${reader.result}')`;
        };
        
        // Actualizar input hidden (útil para lógica antigua o feedback)
        hiddenInput.value = file.name; 
    }
});