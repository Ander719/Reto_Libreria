document.addEventListener('DOMContentLoaded', () => {
    // --- REFERENCIAS DOM ---
    const adjustDataBtn = document.getElementById('adjustData');
    const adminPanelSection = document.getElementById('adminPanelSection');
    const tableBody = document.getElementById('adminTableBody');
    
    // Modales
    const modifyUserPopup = document.getElementById('modifyUserPopupAdmin');
    const modifyAdminPopup = document.getElementById('modifyAdminPopup');
    
    // Botones de Guardar
    const saveBtnUser = document.getElementById('saveBtnUser');
    const saveBtnAdmin = document.getElementById('saveBtnAdmin');
    
    // Botones de Cerrar
    const closeUser = document.getElementById('closeUserModal');
    const closeAdmin = document.getElementById('closeAdminModal');

    // Variable global para almacenar usuarios cargados
    window.allUsers = [];

    // 1. INICIO
    checkUserRole(); // Muestra tabla si eres admin

    // 2. EVENTOS DE APERTURA (Perfil Propio)
    if (adjustDataBtn) {
        adjustDataBtn.addEventListener('click', (e) => {
            e.preventDefault();
            // Limpiamos cualquier ID guardado para indicar que es "Mi Perfil"
            if(saveBtnUser) saveBtnUser.removeAttribute('data-target-id');
            if(saveBtnAdmin) saveBtnAdmin.removeAttribute('data-target-id');
            loadMyProfile();
        });
    }

    const modifySelfBtn = document.getElementById('modifySelfButton');
    if (modifySelfBtn) {
        modifySelfBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if(saveBtnAdmin) saveBtnAdmin.removeAttribute('data-target-id');
            loadMyProfile();
        });
    }

    // 3. EVENTOS DE GUARDADO (La pieza que faltaba)
    if (saveBtnUser) {
        saveBtnUser.addEventListener('click', (e) => {
            e.preventDefault();
            saveUserData('user'); 
        });
    }
    if (saveBtnAdmin) {
        saveBtnAdmin.addEventListener('click', (e) => {
            e.preventDefault();
            saveUserData('admin');
        });
    }

    // Cerrar Modales
    if(closeUser) closeUser.onclick = () => modifyUserPopup.style.display = 'none';
    if(closeAdmin) closeAdmin.onclick = () => modifyAdminPopup.style.display = 'none';
    window.onclick = (e) => {
        if(e.target === modifyUserPopup) modifyUserPopup.style.display = 'none';
        if(e.target === modifyAdminPopup) modifyAdminPopup.style.display = 'none';
    };

    // --- FUNCIONES ---

    function checkUserRole() {
        if(adminPanelSection) adminPanelSection.style.display = 'none';

        fetch('../../api/CheckUserType.php')
            .then(res => res.json())
            .then(data => {
                if (data.isAdmin === true) {
                    if(adminPanelSection) {
                        adminPanelSection.style.display = 'flex';
                        loadUsers(); // Cargar tabla si es admin
                    }
                }
            })
            .catch(console.error);
    }

    function loadUsers() {
        if (!tableBody) return;
        tableBody.innerHTML = '<tr><td colspan="4">Cargando...</td></tr>';
        
        // LEER EL MODO DE LA URL (?mode=deleteUser o ?mode=modifyUser)
        const urlParams = new URLSearchParams(window.location.search);
        const mode = urlParams.get('mode'); 

        fetch('../../api/GetAllUsers.php')
            .then(res => res.json())
            .then(data => {
                tableBody.innerHTML = '';
                const users = data.resultado || [];
                window.allUsers = users; // Guardar en memoria

                if(users.length > 0){
                    users.forEach((user, index) => {
                        const tr = document.createElement('tr');
                        const userId = user.PROFILE_CODE || user.id;
                        
                        // Lógica de botones según modo
                        let buttonsHTML = '';
                        
                        if (mode === 'modifyUser') {
                            // Modo Modificar: Botón Edit
                            buttonsHTML = `<button class="saveBtn" style="padding:5px 10px; font-size:0.8rem;" onclick="prepareEditUser(${index})">Edit</button>`;
                        } else if (mode === 'deleteUser') {
                            // Modo Borrar: Botón Delete
                            buttonsHTML = `<button class="deleteBtn" style="padding:5px 10px; font-size:0.8rem; color:red; border-color:red;" onclick="deleteUser('${userId}')">Del</button>`;
                        } else {
                            // Sin modo (o ambos): Muestra los dos
                            buttonsHTML = `
                                <button class="saveBtn" style="padding:5px 10px; font-size:0.8rem; margin-right:5px;" onclick="prepareEditUser(${index})">Edit</button>
                                <button class="deleteBtn" style="padding:5px 10px; font-size:0.8rem; color:red; border-color:red;" onclick="deleteUser('${userId}')">Del</button>
                            `;
                        }

                        tr.innerHTML = `
                            <td>${user.USER_NAME || user.username}</td>
                            <td>${user.NAME_ || user.name} ${user.SURNAME || user.surname}</td>
                            <td>${user.EMAIL || user.email}</td>
                            <td>${buttonsHTML}</td>
                        `;
                        tableBody.appendChild(tr);
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="4">No hay usuarios.</td></tr>';
                }
            });
    }

    // Prepara el modal para editar a OTRO usuario de la tabla
    window.prepareEditUser = function(index) {
        const u = window.allUsers[index];
        if (!u) return;
        
        // Rellenar formulario usuario (asumimos que en tabla editas usuarios normales)
        setValue('firstNameUser', u.NAME_ || u.name);
        setValue('lastNameUser', u.SURNAME || u.surname);
        setValue('emailUser', u.EMAIL || u.email);
        setValue('usernameUser', u.USER_NAME || u.username);
        setValue('phoneUser', u.TELEPHONE || u.telephone);
        setValue('cardNumberUser', u.CARD_NO || '');
        if(document.getElementById('genderUser')) document.getElementById('genderUser').value = u.GENDER || 'Other';

        // IMPORTANTE: Pegamos el ID al botón de guardar para saber a quién actualizar
        if(saveBtnUser) saveBtnUser.setAttribute('data-target-id', u.PROFILE_CODE);

        document.getElementById('modifyUserPopupAdmin').style.display = 'flex';
    };

    function loadMyProfile() {
        fetch('../../api/GetProfile.php')
            .then(res => res.json())
            .then(data => {
                if (data.exito && data.user) {
                    const u = data.user;
                    const isAdmin = (u.ROLE_TYPE === 'admin') || (u.CURRENT_ACCOUNT !== undefined);

                    if (isAdmin) {
                        setValue('firstNameAdmin', u.NAME_ || u.name);
                        setValue('lastNameAdmin', u.SURNAME || u.surname);
                        setValue('emailAdmin', u.EMAIL || u.email);
                        setValue('usernameAdmin', u.USER_NAME || u.username);
                        setValue('phoneAdmin', u.TELEPHONE || u.telephone);
                        setValue('profileCodeAdmin', u.PROFILE_CODE);
                        setValue('currentAccountAdmin', u.CURRENT_ACCOUNT);
                        document.getElementById('modifyAdminPopup').style.display = 'flex';
                    } else {
                        setValue('firstNameUser', u.NAME_ || u.name);
                        setValue('lastNameUser', u.SURNAME || u.surname);
                        setValue('emailUser', u.EMAIL || u.email);
                        setValue('usernameUser', u.USER_NAME || u.username);
                        setValue('phoneUser', u.TELEPHONE || u.telephone);
                        setValue('cardNumberUser', u.CARD_NO);
                        if(document.getElementById('genderUser')) document.getElementById('genderUser').value = u.GENDER || 'Other';
                        document.getElementById('modifyUserPopupAdmin').style.display = 'flex';
                    }
                } else {
                    alert("Error cargando perfil.");
                }
            });
    }

    // --- FUNCIÓN UNIFICADA PARA GUARDAR ---
    function saveUserData(role) {
        let formData = new FormData();
        let targetId = null;

        if (role === 'admin') {
            // Recoger datos del form Admin
            formData.append('name', document.getElementById('firstNameAdmin').value);
            formData.append('surname', document.getElementById('lastNameAdmin').value);
            formData.append('email', document.getElementById('emailAdmin').value);
            formData.append('phone', document.getElementById('phoneAdmin').value);
            formData.append('accountNumber', document.getElementById('currentAccountAdmin').value);
            
            targetId = document.getElementById('saveBtnAdmin').getAttribute('data-target-id');
        } else {
            // Recoger datos del form Usuario
            formData.append('name', document.getElementById('firstNameUser').value);
            formData.append('surname', document.getElementById('lastNameUser').value);
            formData.append('email', document.getElementById('emailUser').value);
            formData.append('phone', document.getElementById('phoneUser').value);
            formData.append('cardNumber', document.getElementById('cardNumberUser').value);
            formData.append('gender', document.getElementById('genderUser').value);
            
            targetId = document.getElementById('saveBtnUser').getAttribute('data-target-id');
        }

        // Si hay targetId, estamos editando a otro. Si no, editamos "mi perfil" (el PHP lo sabrá por sesión o null)
        if (targetId) {
            formData.append('target_id', targetId);
        }
        formData.append('role', role); // 'user' o 'admin' para saber qué campos actualizar

        fetch('../../api/UpdateUser.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                alert("Datos guardados correctamente.");
                // Recargar tabla por si acaso cambiamos algo visible
                loadUsers();
                // Cerrar modales
                document.getElementById('modifyUserPopupAdmin').style.display = 'none';
                document.getElementById('modifyAdminPopup').style.display = 'none';
            } else {
                alert("Error al guardar: " + data.error);
            }
        })
        .catch(err => alert("Error de conexión."));
    }

    function setValue(id, val) {
        const el = document.getElementById(id);
        if(el) el.value = val || '';
    }

    // --- FUNCIÓN UNIFICADA PARA GUARDAR ---
    function saveUserData(role) {
        let formData = new FormData();
        let targetId = null;

        if (role === 'admin') {
            // Datos Admin
            formData.append('name', document.getElementById('firstNameAdmin').value);
            formData.append('surname', document.getElementById('lastNameAdmin').value);
            formData.append('email', document.getElementById('emailAdmin').value);
            formData.append('username', document.getElementById('usernameAdmin').value); // AÑADIDO: Necesario para el controlador
            formData.append('phone', document.getElementById('phoneAdmin').value);
            formData.append('accountNumber', document.getElementById('currentAccountAdmin').value);
            
            targetId = document.getElementById('saveBtnAdmin').getAttribute('data-target-id');
        } else {
            // Datos Usuario
            formData.append('name', document.getElementById('firstNameUser').value);
            formData.append('surname', document.getElementById('lastNameUser').value);
            formData.append('email', document.getElementById('emailUser').value);
            formData.append('username', document.getElementById('usernameUser').value); // AÑADIDO: Necesario para el controlador
            formData.append('phone', document.getElementById('phoneUser').value);
            formData.append('cardNumber', document.getElementById('cardNumberUser').value);
            
            // Verificación extra por si el elemento gender no existe
            let genderVal = 'Other';
            if(document.getElementById('genderUser')) genderVal = document.getElementById('genderUser').value;
            formData.append('gender', genderVal);
            
            targetId = document.getElementById('saveBtnUser').getAttribute('data-target-id');
        }

        if (targetId) {
            formData.append('target_id', targetId);
        }
        formData.append('role', role);

        // CAMBIO AQUÍ: Apuntamos a tu archivo modifyUser.php
        fetch('../../api/modifyUser.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                alert("Datos guardados correctamente.");
                loadUsers(); // Recargar tabla
                document.getElementById('modifyUserPopupAdmin').style.display = 'none';
                document.getElementById('modifyAdminPopup').style.display = 'none';
            } else {
                alert("Error al guardar: " + (data.error || "Desconocido"));
            }
        })
        .catch(err => alert("Error de conexión."));
    }
    window.deleteUser = function(id) {
        if(confirm("¿Eliminar usuario?")) {
            fetch(`../../api/DeleteUser.php?id=${id}`)
                .then(r => r.json())
                .then(d => {
                    if(d.result) loadUsers();
                    else alert("Error al eliminar");
                });
        }
    };
});