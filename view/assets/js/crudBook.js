document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const mode = urlParams.get('mode'); 
    
    // Referencias DOM... (igual que antes)
    const pageTitle = document.getElementById('pageTitle');
    const searchSection = document.getElementById('searchSection');
    const actionBtn = document.getElementById('actionBtn');
    const isbnInput = document.getElementById('isbn');
    const formInputs = document.querySelectorAll('#bookForm input:not(#isbn), #bookForm textarea');
    const msgSearch = document.getElementById('searchMessage');

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
        } else if (mode === 'delete') {
            pageTitle.innerText = "Eliminar Libro";
            searchSection.style.display = "block";
            actionBtn.innerText = "Confirmar Eliminación";
            actionBtn.style.backgroundColor = "#d9534f"; 
            isbnInput.readOnly = true;
            toggleForm(true);
        }
    }

    // --- BUSCAR (GET) ---
    document.getElementById('btnSearch').addEventListener('click', (e) => {
        e.preventDefault();
        const isbnToSearch = document.getElementById('searchIsbn').value;
        if (!isbnToSearch) return;

        fetch(`../../api/GetBook.php?isbn=${isbnToSearch}`) 
            .then(res => {
                if (!res.ok) throw new Error("Error en la conexión con el servidor");
                return res.json();
            })
            .then(data => {
                if (data.exito) { 
                    fillForm(data.resultado);
                    msgSearch.innerText = "";
                    
                    if (mode === 'edit') toggleForm(false);
                    else if (mode === 'delete') actionBtn.disabled = false;
                } else {
                    msgSearch.innerText = "Libro no encontrado.";
                    document.getElementById('bookForm').reset();
                    // Restaurar el ISBN que el usuario escribió para que no se borre
                    document.getElementById('searchIsbn').value = isbnToSearch; 
                    if(mode !== 'create') toggleForm(true);
                }
            })
            .catch(err => {
                console.error(err);
                alert("Error al buscar el libro. Revisa la consola (F12).");
            });
    });

    const form = document.getElementById('bookForm');

    form.addEventListener('submit', (e) => {
        e.preventDefault(); // Evita que la página se recargue

        console.log("¡Botón pulsado! Procesando formulario...");

        // 1. VALIDACIÓN DEL MODO
        if (!mode) {
            alert("Error Crítico: No se sabe si estás creando o editando.\n\nAsegúrate de que la URL termina en ?mode=create o ?mode=edit");
            return;
        }

        // 2. Confirmación si es borrar
        if(mode === 'delete' && !confirm("¿Seguro que deseas eliminar este libro?")) return;

        // 3. Preparar datos
        const formData = new FormData(e.target);
        const dataObj = Object.fromEntries(formData.entries());

        // Asegurarse de que el input 'cover' tiene valor (del input hidden)
        dataObj.cover = document.getElementById('cover').value;

        // 4. Seleccionar URL
        let url = '';
        if (mode === 'create') {
            url = '../../api/AddBook.php';
        } else if (mode === 'edit') {
            url = '../../api/ModifyBook.php';
        } else if (mode === 'delete') {
            // Lógica especial para delete
            fetch(`../../api/DeleteBook.php?isbn=${dataObj.isbn}`)
                .then(res => res.json())
                .then(response => {
                    if(response.result === true) {
                        alert("Libro eliminado correctamente");
                        window.location.href = 'bookOptions.html';
                    } else {
                        alert("Error al eliminar: " + (response.error || "Desconocido"));
                    }
                })
                .catch(err => console.error("Error en delete:", err));
            return; // Salimos porque delete va por GET (o fetch distinto)
        }

        // 5. VALIDACIÓN DE URL
        if (!url) {
            alert("Error: No hay URL de destino. Revisa el código del 'mode'.");
            return;
        }

        console.log("Enviando a:", url, dataObj);

        // 6. ENVIAR DATOS
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dataObj)
        })
        .then(res => {
            if (!res.ok) throw new Error(`Error HTTP: ${res.status}`);
            return res.json();
        })
        .then(data => {
            console.log("Respuesta servidor:", data);
            if(data.exito) {
                alert(data.message || "Operación exitosa");
                window.location.href = 'bookOptions.html';
            } else {
                alert("El servidor reportó un error: " + (data.error || "Error desconocido"));
            }
        })
        .catch(err => {
            console.error("Error Fetch:", err);
            alert("Error de conexión. Mira la consola (F12) para más detalles.");
        });
    });

    function toggleForm(isDisabled) {
        formInputs.forEach(input => input.disabled = isDisabled);
        if (mode !== 'create') actionBtn.disabled = isDisabled;
    }

    function fillForm(data) {
        document.getElementById('isbn').value = data.isbn;
        document.getElementById('title').value = data.title;
        document.getElementById('author').value = data.author;
        document.getElementById('pages').value = data.pages;
        document.getElementById('stock').value = data.stock;
        document.getElementById('price').value = data.price;
        document.getElementById('editorial').value = data.editorial;
        document.getElementById('cover').value = data.cover;
        document.getElementById('synopsis').value = data.synopsis;
    }
    /* ------------------------------------------------------
       LÓGICA DRAG & DROP PARA LA PORTADA
       ------------------------------------------------------ */
    const dropZone = document.getElementById("dropZone");
    const inputElement = document.getElementById("coverInput");
    const hiddenInput = document.getElementById("cover"); // El que se envía a la BD

    // 1. Al hacer clic en la zona, abrimos el selector de archivos
    dropZone.addEventListener("click", (e) => {
        inputElement.click();
    });

    // 2. Al seleccionar un archivo manualmente
    inputElement.addEventListener("change", (e) => {
        if (inputElement.files.length) {
            updateThumbnail(dropZone, inputElement.files[0]);
        }
    });

    // 3. Eventos de arrastre (Drag over)
    dropZone.addEventListener("dragover", (e) => {
        e.preventDefault(); // Necesario para permitir soltar
        dropZone.classList.add("drop-zone--over");
    });

    // 4. Eventos de salir del arrastre (Drag leave)
    ["dragleave", "dragend"].forEach((type) => {
        dropZone.addEventListener(type, (e) => {
            dropZone.classList.remove("drop-zone--over");
        });
    });

    // 5. Al soltar el archivo (Drop)
    dropZone.addEventListener("drop", (e) => {
        e.preventDefault();

        // Si hay archivos arrastrados
        if (e.dataTransfer.files.length) {
            inputElement.files = e.dataTransfer.files; // Asignamos los archivos al input real
            updateThumbnail(dropZone, e.dataTransfer.files[0]);
        }

        dropZone.classList.remove("drop-zone--over");
    });

    /**
     * Función para mostrar la vista previa de la imagen
     */
    function updateThumbnail(dropZoneElement, file) {
        let thumbnailElement = dropZoneElement.querySelector(".drop-zone__thumb");

        // Eliminamos el texto de ayuda si existe
        if (dropZoneElement.querySelector(".drop-zone__prompt")) {
            dropZoneElement.querySelector(".drop-zone__prompt").remove();
        }

        // Si no existe el elemento de imagen, lo creamos
        if (!thumbnailElement) {
            thumbnailElement = document.createElement("div");
            thumbnailElement.classList.add("drop-zone__thumb");
            dropZoneElement.appendChild(thumbnailElement);
        }

        // Ponemos el nombre del archivo como etiqueta
        thumbnailElement.dataset.label = file.name;

        // Leemos la imagen para mostrarla
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => {
            thumbnailElement.style.backgroundImage = `url('${reader.result}')`;
        };
        
        // IMPORTANTE: Guardamos el NOMBRE del archivo en el input oculto
        // para que tu lógica actual de PHP siga funcionando y guarde el texto en la BD.
        hiddenInput.value = file.name; 
    }
});