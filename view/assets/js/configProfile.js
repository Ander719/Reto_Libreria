// view/assets/js/configProfile.js
import { checkSession, currentUser } from './sesion.js';

document.addEventListener('DOMContentLoaded', async () => {

    const isLogged = await checkSession();

    if (isLogged) {
        console.log("Usuario logueado:", currentUser);
    } else {
        console.log("No hay sesión activa");
    }
    
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

    // 1. INICIO: Comprobar rol para mostrar tabla
    checkUserRole(); 

    // 2. EVENTOS DE APERTURA (Perfil Propio)
    if (adjustDataBtn) {
        adjustDataBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if(saveBtnUser) saveBtnUser.removeAttribute('data-target-id');
            if(saveBtnAdmin) saveBtnAdmin.removeAttribute('data-target-id');
            loadMyProfile();
        });
    }

    // 3. EVENTOS DE GUARDADO
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

        // CORRECCIÓN IMPORTANTE: credentials: 'include'
        fetch('../../api/CheckUserType.php', { credentials: 'include' })
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
        
        // 1. CAPTURAR EL MODO DE LA URL
        const urlParams = new URLSearchParams(window.location.search);
        const mode = urlParams.get('mode'); // Esperamos 'modifyUser' o 'deleteUser'

        // Añadimos credentials: 'include' para asegurar la sesión
        fetch('../../api/GetAllUsers.php', { credentials: 'include' })
            .then(res => res.json())
            .then(data => {
                tableBody.innerHTML = '';
                const users = data.resultado || [];
                window.allUsers = users; 

                if(users.length > 0){
                    users.forEach((user, index) => {
                        const tr = document.createElement('tr');
                        
                        // Mapeo seguro de datos (minúsculas/mayúsculas)
                        const userId = user.profile_code || user.PROFILE_CODE;
                        const userName = user.user_name || user.USER_NAME;
                        const name = user.name_ || user.NAME_;
                        const surname = user.surname || user.SURNAME;
                        const email = user.email || user.EMAIL;
                        
                        // 2. LÓGICA DE BOTONES (SEGÚN EL MODO)
                        let buttonsHTML = '';
                        
                        if (mode === 'modifyUser') {
                            // MODO EDITAR: Solo botón Edit
                            buttonsHTML = `<button class="saveBtn" style="padding:5px 10px; font-size:0.8rem;" onclick="prepareEditUser(${index})">Edit</button>`;
                        
                        } else if (mode === 'deleteUser') {
                            // MODO ELIMINAR: Solo botón Del
                            buttonsHTML = `<button class="deleteBtn" style="padding:5px 10px; font-size:0.8rem; color:red; border-color:red;" onclick="deleteUser('${userId}')">Del</button>`;
                        
                        } else {
                            // MODO POR DEFECTO: Ambos botones (útil para pruebas)
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
                    tableBody.innerHTML = '<tr><td colspan="4">No hay usuarios registrados.</td></tr>';
                }
            })
            .catch(err => {
                console.error(err);
                tableBody.innerHTML = '<tr><td colspan="4">Error al cargar usuarios.</td></tr>';
            });
    }

    window.prepareEditUser = function(index) {
        const u = window.allUsers[index];
        if (!u) return;
        
        setValue('firstNameUser', u.name_);
        setValue('lastNameUser', u.surname);
        setValue('emailUser', u.email);
        setValue('usernameUser', u.user_name);
        setValue('phoneUser', u.telephone);
        setValue('cardNumberUser', u.card_no);
        
        if(document.getElementById('genderUser')) {
            document.getElementById('genderUser').value = u.gender || 'Other';
        }

        // Guardamos el ID del usuario que vamos a editar en el botón
        if(saveBtnUser) saveBtnUser.setAttribute('data-target-id', u.profile_code);

        document.getElementById('modifyUserPopupAdmin').style.display = 'flex';
    };

    function loadMyProfile() {
        fetch('../../api/GetProfile.php', { credentials: 'include' }) 
            .then(res => res.json())
            .then(data => {
                if (data.exito && data.user) {
                    const u = data.user;
                    
                    // Detectar Admin
                    const isAdmin = (u.role_type === 'admin' || (u.current_account !== undefined && u.current_account !== null));

                    if (isAdmin) {
                        setValue('firstNameAdmin', u.name_);
                        setValue('lastNameAdmin', u.surname);
                        setValue('emailAdmin', u.email);
                        setValue('usernameAdmin', u.user_name);
                        setValue('phoneAdmin', u.telephone);
                        setValue('profileCodeAdmin', u.profile_code);
                        setValue('currentAccountAdmin', u.current_account);
                        
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

        if (targetId) {
            formData.append('target_id', targetId);
        }
        formData.append('role', role);

        fetch('../../api/ModifyUser.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                alert("Datos guardados correctamente.");
                loadUsers(); 
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