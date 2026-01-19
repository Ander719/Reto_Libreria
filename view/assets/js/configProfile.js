import { checkSession, currentUser } from './session.js'; // Ojo: session.js o sesion.js (verifica el nombre)

let currentEditingUser = {
    id: null,
    username: null
};

document.addEventListener('DOMContentLoaded', async () => {

    // 1. Verificar sesión
    const isLogged = await checkSession();
    if (!isLogged) {
        console.log("No hay sesión activa");
        // Opcional: window.location.href = 'login.html';
    }

    // --- REFERENCIAS ---
    const adjustDataBtn = document.getElementById('adjustData');
    const adminPanelSection = document.getElementById('adminPanelSection');
    const tableBody = document.getElementById('adminTableBody');
    const saveBtnUser = document.getElementById('saveBtnUser');
    const saveBtnAdmin = document.getElementById('saveBtnAdmin');
    
    // Modales y Botones de Password
    const verifyModal = document.getElementById('verifyPasswordModal');
    const changeModal = document.getElementById('changePasswordModal');
    const changePwdBtn = document.getElementById('changePwdBtn');
    const changePwdBtnAdmin = document.getElementById('changePwdBtnAdmin');
    const verifyForm = document.getElementById('verifyPasswordForm');
    const changeForm = document.getElementById('changePasswordForm');
    const btnCancelVerify = document.getElementById('btnCancelVerify');
    
    // Cerrar Modales
    const closeUser = document.getElementById('closeUserModal');
    const closeAdmin = document.getElementById('closeAdminModal');
    const closeVerify = document.getElementById('closeVerifyModal');
    const closeChange = document.getElementById('closeChangeModal');
    const modifyUserPopup = document.getElementById('modifyUserPopupAdmin');
    const modifyAdminPopup = document.getElementById('modifyAdminPopup');

    window.allUsers = [];

    // 2. INICIO
    checkUserRole(); 

    // 3. EVENTO MI PERFIL
    if (adjustDataBtn) {
        adjustDataBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if(saveBtnUser) saveBtnUser.removeAttribute('data-target-id');
            if(saveBtnAdmin) saveBtnAdmin.removeAttribute('data-target-id');
            loadMyProfile();
        });
    }

    // 4. GUARDAR PERFIL
    if (saveBtnUser) saveBtnUser.onclick = (e) => { e.preventDefault(); saveUserData('user'); };
    if (saveBtnAdmin) saveBtnAdmin.onclick = (e) => { e.preventDefault(); saveUserData('admin'); };

    // ==========================================
    // LÓGICA DE CONTRASEÑA (DOBLE PASO)
    // ==========================================
    
    // A) Abrir Modal
    const openVerifyModal = (e) => {
        e.preventDefault();
        if(document.getElementById('verifyCurrentPassword')) 
            document.getElementById('verifyCurrentPassword').value = '';
        if(verifyModal) verifyModal.style.display = 'flex';
    };

    if(changePwdBtn) changePwdBtn.onclick = openVerifyModal;
    if(changePwdBtnAdmin) changePwdBtnAdmin.onclick = openVerifyModal;

    // B) Cerrar Modales
    const closeModals = () => {
        if(verifyModal) verifyModal.style.display = 'none';
        if(changeModal) changeModal.style.display = 'none';
    };
    if(btnCancelVerify) btnCancelVerify.onclick = closeModals;
    if(closeVerify) closeVerify.onclick = closeModals;
    if(closeChange) closeChange.onclick = closeModals;

    // C) Paso 1: Verificar
    if(verifyForm) {
        verifyForm.onsubmit = async (e) => {
            e.preventDefault();
            const pass = document.getElementById('verifyCurrentPassword').value;
            // Usamos el username global o el del input visible
            let username = currentEditingUser.username || document.getElementById('usernameUser').value || document.getElementById('usernameAdmin').value;
            
            try {
                // Reutilizamos Login.php para verificar
                const res = await fetch('../../api/Login.php', {
                    method: 'POST',
                    body: JSON.stringify({ username: username, password: pass })
                });
                const data = await res.json();
                
                // Login.php devuelve 'success' o 'exito' dependiendo de tu versión, comprobamos ambos
                if (data.success || data.exito) {
                    verifyModal.style.display = 'none';
                    document.getElementById('newPassword').value = '';
                    document.getElementById('confirmNewPassword').value = '';
                    changeModal.style.display = 'flex';
                } else {
                    alert("Contraseña incorrecta.");
                }
            } catch (err) { console.error(err); alert("Error de conexión"); }
        };
    }

    // D) Paso 2: Cambiar
    if(changeForm) {
        changeForm.onsubmit = async (e) => {
            e.preventDefault();
            const newP = document.getElementById('newPassword').value;
            const confP = document.getElementById('confirmNewPassword').value;
            
            if(newP !== confP) return alert("Las contraseñas no coinciden");
            if(newP.length < 4) return alert("Mínimo 4 caracteres");
            
            try {
                const res = await fetch('../../api/ModifyPassword.php', {
                    method: 'POST',
                    body: JSON.stringify({ profile_code: currentEditingUser.id, password: newP })
                });
                const data = await res.json();
                if(data.success) {
                    alert("Contraseña cambiada.");
                    closeModals();
                } else {
                    alert("Error: " + data.error);
                }
            } catch(err) { console.error(err); }
        };
    }

    // ==========================================
    // FUNCIONES AUXILIARES
    // ==========================================

    function checkUserRole() {
        if(adminPanelSection) adminPanelSection.style.display = 'none';
        const urlParams = new URLSearchParams(window.location.search);
        const mode = urlParams.get('mode');

        // CheckUserType.php debe devolver si es admin
        fetch('../../api/CheckUserType.php', { credentials: 'include' })
            .then(r => r.json())
            .then(data => {
                // Ajusta esto según lo que devuelva tu nuevo CheckUserType (isAdmin o role === 'admin')
                if (data.isAdmin === true || (data.user && data.user.role === 'admin')) {
                    if(adminPanelSection) {
                        adminPanelSection.style.display = 'flex';
                        loadUsers(mode);
                    }
                }
            });
    }

    function loadUsers(mode) {
        if (!tableBody) return;
        tableBody.innerHTML = '<tr><td colspan="4">Cargando...</td></tr>';
        
        fetch('../../api/GetAllUsers.php')
            .then(r => r.json())
            .then(data => {
                tableBody.innerHTML = '';
                const users = data.resultado || data.users || []; // Ajustar según respuesta
                window.allUsers = users;

                users.forEach((u, index) => {
                    const tr = document.createElement('tr');
                    // Usamos profile_code (nueva BD)
                    const uid = u.profile_code;
                    const uname = u.user_name;
                    
                    let btns = '';
                    if (mode === 'modifyUser') btns = `<button class="saveBtn" onclick="prepareEditUser(${index})">Edit</button>`;
                    else if (mode === 'deleteUser') btns = `<button class="deleteBtn" style="color:red" onclick="deleteUser('${uid}')">Del</button>`;
                    else btns = `<button class="saveBtn" onclick="prepareEditUser(${index})">Edit</button> <button class="deleteBtn" style="color:red" onclick="deleteUser('${uid}')">Del</button>`;

                    tr.innerHTML = `<td>${uname}</td><td>${u.name_} ${u.surname}</td><td>${u.email}</td><td>${btns}</td>`;
                    tableBody.appendChild(tr);
                });
            });
    }

    window.prepareEditUser = function(index) {
        const u = window.allUsers[index];
        if (!u) return;
        
        currentEditingUser.id = u.profile_code;
        currentEditingUser.username = u.user_name;

        setValue('firstNameUser', u.name_);
        setValue('lastNameUser', u.surname);
        setValue('emailUser', u.email);
        setValue('usernameUser', u.user_name);
        setValue('phoneUser', u.telephone);
        setValue('cardNumberUser', u.card_no);
        
        if(saveBtnUser) saveBtnUser.setAttribute('data-target-id', u.profile_code);
        document.getElementById('modifyUserPopupAdmin').style.display = 'flex';
    };

    function loadMyProfile() {
        fetch('../../api/GetProfile.php')
            .then(r => r.json())
            .then(data => {
                if(data.exito && data.user) {
                    const u = data.user;
                    currentEditingUser.id = u.profile_code;
                    currentEditingUser.username = u.user_name;

                    const isAdmin = (u.role_type === 'admin' || u.current_account);
                    
                    if(isAdmin) {
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
                        if(saveBtnUser) saveBtnUser.setAttribute('data-target-id', u.profile_code);
                        document.getElementById('modifyUserPopupAdmin').style.display = 'flex';
                    }
                }
            });
    }

    function saveUserData(role) {
        let formData = new FormData();
        let targetId = (role === 'admin') 
            ? document.getElementById('saveBtnAdmin').getAttribute('data-target-id')
            : document.getElementById('saveBtnUser').getAttribute('data-target-id');
            
        // ... (Tu lógica de FormData sigue igual, solo asegúrate de apuntar a los IDs correctos)
        // Ejemplo simplificado:
        if(role === 'user') {
            formData.append('name', document.getElementById('firstNameUser').value);
            formData.append('surname', document.getElementById('lastNameUser').value);
            formData.append('email', document.getElementById('emailUser').value);
            formData.append('username', document.getElementById('usernameUser').value);
            formData.append('phone', document.getElementById('phoneUser').value);
            formData.append('cardNumber', document.getElementById('cardNumberUser').value);
            formData.append('gender', document.getElementById('genderUser') ? document.getElementById('genderUser').value : 'Other');
        } else {
             formData.append('name', document.getElementById('firstNameAdmin').value);
             formData.append('surname', document.getElementById('lastNameAdmin').value);
             formData.append('email', document.getElementById('emailAdmin').value);
             formData.append('username', document.getElementById('usernameAdmin').value);
             formData.append('phone', document.getElementById('phoneAdmin').value);
             formData.append('accountNumber', document.getElementById('currentAccountAdmin').value);
        }

        if(targetId) formData.append('target_id', targetId);
        formData.append('role', role);

        fetch('../../api/ModifyUser.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if(data.exito) {
                    alert("Guardado");
                    document.getElementById('modifyUserPopupAdmin').style.display = 'none';
                    document.getElementById('modifyAdminPopup').style.display = 'none';
                    loadUsers(new URLSearchParams(window.location.search).get('mode'));
                } else {
                    alert("Error: " + data.error);
                }
            });
    }
    
    function setValue(id, v) { 
        const el = document.getElementById(id); 
        if(el) el.value = v || ''; 
    }
    
    // Cerrar Modales (X)
    const closeMain = (m) => { if(m) m.style.display = 'none'; };
    if(closeUser) closeUser.onclick = () => closeMain(modifyUserPopup);
    if(closeAdmin) closeAdmin.onclick = () => closeMain(modifyAdminPopup);
});