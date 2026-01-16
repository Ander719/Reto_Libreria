import { checkSession, currentUser } from './sesion.js';

// Estado global para saber a quién estamos editando (ID y Username)
// Necesario para verificar la contraseña del usuario correcto
let currentEditingUser = {
    id: null,
    username: null
};

document.addEventListener('DOMContentLoaded', async () => {

    // 1. VERIFICACIÓN DE SESIÓN INICIAL
    const isLogged = await checkSession();
    if (isLogged) {
        console.log("Sesión activa:", currentUser);
    } else {
        console.log("No hay sesión activa");
    }

    // --- REFERENCIAS DOM ---
    const adjustDataBtn = document.getElementById('adjustData');
    const adminPanelSection = document.getElementById('adminPanelSection');
    const tableBody = document.getElementById('adminTableBody');
    
    // Modales de Perfil
    const modifyUserPopup = document.getElementById('modifyUserPopupAdmin');
    const modifyAdminPopup = document.getElementById('modifyAdminPopup');
    
    // Modales de Contraseña (Nuevos)
    const verifyModal = document.getElementById('verifyPasswordModal');
    const changeModal = document.getElementById('changePasswordModal');
    
    // Botones de Guardar Datos
    const saveBtnUser = document.getElementById('saveBtnUser');
    const saveBtnAdmin = document.getElementById('saveBtnAdmin');
    
    // Botones para iniciar cambio de contraseña
    const changePwdBtn = document.getElementById('changePwdBtn');         // En form usuario
    const changePwdBtnAdmin = document.getElementById('changePwdBtnAdmin'); // En form admin

    // Formularios de Contraseña
    const verifyForm = document.getElementById('verifyPasswordForm');
    const changeForm = document.getElementById('changePasswordForm');
    const btnCancelVerify = document.getElementById('btnCancelVerify');

    // Botones de Cerrar (X)
    const closeUser = document.getElementById('closeUserModal');
    const closeAdmin = document.getElementById('closeAdminModal');
    const closeVerify = document.getElementById('closeVerifyModal');
    const closeChange = document.getElementById('closeChangeModal');

    // Variable global para la tabla
    window.allUsers = [];

    // 2. INICIO: CARGAR TABLA SI ES ADMIN
    checkUserRole(); 

    // 3. EVENTO: ABRIR MI PERFIL ("Adjust Data")
    if (adjustDataBtn) {
        adjustDataBtn.addEventListener('click', (e) => {
            e.preventDefault();
            // Limpiamos target-id para indicar que es edición propia
            if(saveBtnUser) saveBtnUser.removeAttribute('data-target-id');
            if(saveBtnAdmin) saveBtnAdmin.removeAttribute('data-target-id');
            loadMyProfile();
        });
    }

    // 4. EVENTOS: GUARDAR DATOS DEL PERFIL
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

    // ======================================================
    // 5. LÓGICA DE CAMBIO DE CONTRASEÑA (DOBLE PASO)
    // ======================================================

    // A) Abrir el primer modal (Verificación)
    function openVerifyModal(e) {
        e.preventDefault();
        // Limpiar campo anterior
        const inputVerify = document.getElementById('verifyCurrentPassword');
        if(inputVerify) inputVerify.value = '';
        
        if(verifyModal) verifyModal.style.display = 'flex';
    }

    if(changePwdBtn) changePwdBtn.addEventListener('click', openVerifyModal);
    if(changePwdBtnAdmin) changePwdBtnAdmin.addEventListener('click', openVerifyModal);

    // B) Cerrar Modales
    const closeModals = () => {
        if(verifyModal) verifyModal.style.display = 'none';
        if(changeModal) changeModal.style.display = 'none';
    };

    if(btnCancelVerify) btnCancelVerify.onclick = closeModals;
    if(closeVerify) closeVerify.onclick = closeModals;
    if(closeChange) closeChange.onclick = closeModals;

    // C) PASO 1: Verificar Contraseña Actual
    if(verifyForm) {
        verifyForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const currentPassInput = document.getElementById('verifyCurrentPassword').value;
            
            // Usamos el username cargado en el formulario actual
            let usernameToCheck = currentEditingUser.username;
            
            // Fallback: leer del input si la variable global fallase
            if(!usernameToCheck) {
                usernameToCheck = document.getElementById('usernameUser').value || document.getElementById('usernameAdmin').value;
            }

            if(!usernameToCheck) {
                alert("Error: No se ha identificado el usuario.");
                return;
            }

            try {
                // Usamos Login.php para verificar credenciales
                const response = await fetch('../../api/Login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        username: usernameToCheck, 
                        password: currentPassInput 
                    })
                });
                
                const data = await response.json();

                if (data.exito) {
                    // ÉXITO: Cerramos verificación y abrimos cambio
                    verifyModal.style.display = 'none';
                    
                    // Limpiar campos del paso 2
                    document.getElementById('newPassword').value = '';
                    document.getElementById('confirmNewPassword').value = '';
                    
                    changeModal.style.display = 'flex';
                } else {
                    alert("La contraseña actual es incorrecta. Inténtalo de nuevo.");
                    document.getElementById('verifyCurrentPassword').value = '';
                }
            } catch (err) {
                console.error(err);
                alert("Error de conexión al verificar contraseña.");
            }
        });
    }

    // D) PASO 2: Guardar Nueva Contraseña
    if(changeForm) {
        changeForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const newPass = document.getElementById('newPassword').value;
            const confirmPass = document.getElementById('confirmNewPassword').value;
            const profileCode = currentEditingUser.id; // ID del usuario que estamos editando

            if(!profileCode) {
                alert("Error: ID de usuario no encontrado.");
                return;
            }

            if(newPass !== confirmPass) {
                alert("Las nuevas contraseñas no coinciden.");
                return;
            }
            if(newPass.length < 4) {
                alert("La contraseña es muy corta (mínimo 4 caracteres).");
                return;
            }

            try {
                const response = await fetch('../../api/ModifyPassword.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        profile_code: profileCode, 
                        password: newPass 
                    })
                });
                
                const data = await response.json();

                if (data.success) { // Asegúrate que tu PHP devuelve 'success' o 'exito'
                    alert("¡Contraseña cambiada correctamente!");
                    changeModal.style.display = 'none';
                } else {
                    alert("Error: " + (data.error || "No se pudo cambiar la contraseña."));
                }
            } catch (err) {
                console.error(err);
                alert("Error de conexión con el servidor.");
            }
        });
    }

    // --- CERRAR MODALES PRINCIPALES ---
    const closeMainModals = (modal) => { if(modal) modal.style.display = 'none'; };
    if(closeUser) closeUser.onclick = () => closeMainModals(modifyUserPopup);
    if(closeAdmin) closeAdmin.onclick = () => closeMainModals(modifyAdminPopup);
    
    window.onclick = (e) => {
        if(e.target === modifyUserPopup) modifyUserPopup.style.display = 'none';
        if(e.target === modifyAdminPopup) modifyAdminPopup.style.display = 'none';
        if(e.target === verifyModal) verifyModal.style.display = 'none';
        if(e.target === changeModal) changeModal.style.display = 'none';
    };


    // ======================================================
    // FUNCIONES AUXILIARES
    // ======================================================

    function checkUserRole() {
        if(adminPanelSection) adminPanelSection.style.display = 'none';

        // Detectar modo desde la URL (?mode=deleteUser o modifyUser)
        const urlParams = new URLSearchParams(window.location.search);
        const mode = urlParams.get('mode');

        fetch('../../api/CheckUserType.php', { credentials: 'include' })
            .then(res => res.json())
            .then(data => {
                if (data.isAdmin === true) {
                    if(adminPanelSection) {
                        adminPanelSection.style.display = 'flex';
                        loadUsers(mode); 
                    }
                }
            })
            .catch(console.error);
    }

    function loadUsers(mode) {
        if (!tableBody) return;
        tableBody.innerHTML = '<tr><td colspan="4">Cargando...</td></tr>';
        
        fetch('../../api/GetAllUsers.php', { credentials: 'include' })
            .then(res => res.json())
            .then(data => {
                tableBody.innerHTML = '';
                const users = data.resultado || [];
                window.allUsers = users; 

                if(users.length > 0){
                    users.forEach((user, index) => {
                        const tr = document.createElement('tr');
                        // Datos seguros (minúsculas por defecto, fallback a mayúsculas)
                        const userId = user.profile_code || user.PROFILE_CODE;
                        const userName = user.user_name || user.USER_NAME;
                        const name = user.name_ || user.NAME_;
                        const surname = user.surname || user.SURNAME;
                        const email = user.email || user.EMAIL;
                        
                        let buttonsHTML = '';
                        
                        // Lógica de botones según el modo
                        if (mode === 'modifyUser') {
                            buttonsHTML = `<button class="saveBtn" style="padding:5px 10px; font-size:0.8rem;" onclick="prepareEditUser(${index})">Edit</button>`;
                        } else if (mode === 'deleteUser') {
                            buttonsHTML = `<button class="deleteBtn" style="padding:5px 10px; font-size:0.8rem; color:red; border-color:red;" onclick="deleteUser('${userId}')">Del</button>`;
                        } else {
                            // Por defecto ambos
                            buttonsHTML = `
                                <button class="saveBtn" style="padding:5px 10px; font-size:0.8rem; margin-right:5px;" onclick="prepareEditUser(${index})">Edit</button>
                                <button class="deleteBtn" style="padding:5px 10px; font-size:0.8rem; color:red; border-color:red;" onclick="deleteUser('${userId}')">Del</button>
                            `;
                        }

                        tr.innerHTML = `
                            <td>${userName}</td>
                            <td>${name} ${surname}</td>
                            <td>${email}</td>
                            <td>${buttonsHTML}</td>
                        `;
                        tableBody.appendChild(tr);
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="4">No hay usuarios.</td></tr>';
                }
            });
    }

    // Función global para poder ser llamada desde el HTML onclick
    window.prepareEditUser = function(index) {
        const u = window.allUsers[index];
        if (!u) return;
        
        // ACTUALIZAR ESTADO GLOBAL (Vital para la contraseña)
        currentEditingUser.id = u.profile_code || u.PROFILE_CODE;
        currentEditingUser.username = u.user_name || u.USER_NAME;

        setValue('firstNameUser', u.name_ || u.NAME_);
        setValue('lastNameUser', u.surname || u.SURNAME);
        setValue('emailUser', u.email || u.EMAIL);
        setValue('usernameUser', u.user_name || u.USER_NAME);
        setValue('phoneUser', u.telephone || u.TELEPHONE);
        setValue('cardNumberUser', u.card_no || u.CARD_NO);
        
        if(document.getElementById('genderUser')) {
            document.getElementById('genderUser').value = u.gender || u.GENDER || 'Other';
        }

        if(saveBtnUser) saveBtnUser.setAttribute('data-target-id', currentEditingUser.id);
        
        document.getElementById('modifyUserPopupAdmin').style.display = 'flex';
    };

    function loadMyProfile() {
        fetch('../../api/GetProfile.php', { credentials: 'include' }) 
            .then(res => res.json())
            .then(data => {
                if (data.exito && data.user) {
                    const u = data.user;
                    
                    // ACTUALIZAR ESTADO GLOBAL
                    currentEditingUser.id = u.profile_code;
                    currentEditingUser.username = u.user_name;

                    const isAdmin = (u.role_type === 'admin' || (u.current_account !== undefined && u.current_account !== null));

                    if (isAdmin) {
                        setValue('firstNameAdmin', u.name_);
                        setValue('lastNameAdmin', u.surname);
                        setValue('emailAdmin', u.email);
                        setValue('usernameAdmin', u.user_name);
                        setValue('phoneAdmin', u.telephone);
                        setValue('profileCodeAdmin', u.profile_code);
                        setValue('currentAccountAdmin', u.current_account);
                        
                        if(saveBtnAdmin) saveBtnAdmin.setAttribute('data-target-id', u.profile_code);
                        document.getElementById('modifyAdminPopup').style.display = 'flex';
                    } else {
                        setValue('firstNameUser', u.name_);
                        setValue('lastNameUser', u.surname);
                        setValue('emailUser', u.email);
                        setValue('usernameUser', u.user_name);
                        setValue('phoneUser', u.telephone);
                        setValue('cardNumberUser', u.card_no);
                        
                        if(document.getElementById('genderUser')) {
                            document.getElementById('genderUser').value = u.gender || 'Other';
                        }
                        if(saveBtnUser) saveBtnUser.setAttribute('data-target-id', u.profile_code);
                        document.getElementById('modifyUserPopupAdmin').style.display = 'flex';
                    }
                } else {
                    console.error("Error servidor:", data);
                    alert("Error: " + (data.error || "No se pudieron cargar los datos."));
                }
            })
            .catch(err => {
                console.error(err);
                alert("Error de conexión al cargar perfil.");
            });
    }

    function saveUserData(role) {
        let formData = new FormData();
        let targetId = null;

        if (role === 'admin') {
            formData.append('name', document.getElementById('firstNameAdmin').value);
            formData.append('surname', document.getElementById('lastNameAdmin').value);
            formData.append('email', document.getElementById('emailAdmin').value);
            formData.append('username', document.getElementById('usernameAdmin').value);
            formData.append('phone', document.getElementById('phoneAdmin').value);
            formData.append('accountNumber', document.getElementById('currentAccountAdmin').value);
            targetId = document.getElementById('saveBtnAdmin').getAttribute('data-target-id');
        } else {
            formData.append('name', document.getElementById('firstNameUser').value);
            formData.append('surname', document.getElementById('lastNameUser').value);
            formData.append('email', document.getElementById('emailUser').value);
            formData.append('username', document.getElementById('usernameUser').value);
            formData.append('phone', document.getElementById('phoneUser').value);
            formData.append('cardNumber', document.getElementById('cardNumberUser').value);
            
            let genderVal = 'Other';
            if(document.getElementById('genderUser')) genderVal = document.getElementById('genderUser').value;
            formData.append('gender', genderVal);
            
            targetId = document.getElementById('saveBtnUser').getAttribute('data-target-id');
        }

        if (targetId) formData.append('target_id', targetId);
        formData.append('role', role);

        fetch('../../api/ModifyUser.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                alert("Datos guardados correctamente.");
                loadUsers(); // Recarga la tabla para ver cambios
                
                // Actualizamos sesión local si nos editamos a nosotros mismos
                if(data.message) { 
                    // Opcional: podrías recargar 'loadMyProfile' si el modal sigue abierto
                }
                
                document.getElementById('modifyUserPopupAdmin').style.display = 'none';
                document.getElementById('modifyAdminPopup').style.display = 'none';
            } else {
                alert("Error al guardar: " + (data.error || "Desconocido"));
            }
        })
        .catch(err => alert("Error de conexión."));
    }

    function setValue(id, val) {
        const el = document.getElementById(id);
        if(el) el.value = val || '';
    }

    window.deleteUser = function(id) {
        if(confirm("¿Estás seguro de eliminar este usuario?")) {
            fetch(`../../api/DeleteUser.php?id=${id}`)
                .then(r => r.json())
                .then(d => {
                    if(d.result) {
                        if(d.isSelfDelete) {
                            alert("Tu cuenta ha sido eliminada. Redirigiendo...");
                            window.location.href = 'login.html';
                        } else {
                            loadUsers(); // Recargar tabla
                        }
                    } else {
                        alert("Error al eliminar: " + (d.error || "Desconocido"));
                    }
                })
                .catch(e => alert("Error de conexión"));
        }
    };
});