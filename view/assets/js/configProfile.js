import { checkSession } from './session.js';
import { loadHeader, loadFooter } from './header.js';

const appState = { 
    allUsers: [],
    myProfileCode: null // Para recordar siempre tu propio ID
};

const getEl = (id) => document.getElementById(id);

// Función segura para rellenar inputs
const fill = (id, value) => {
    const el = getEl(id);
    if (el) el.value = (value === null || value === undefined) ? '' : value;
};

// ==========================================
// 1. INICIALIZACIÓN
// ==========================================
document.addEventListener('DOMContentLoaded', async () => {
    const isLogged = await checkSession();
    if (!isLogged) return window.location.href = 'login.html';

    await loadHeader('configProfile');
    await loadFooter();
    
    // Primero cargamos nuestro perfil para conocer nuestro ID
    await loadMyProfile(true); 
    
    setupEventListeners();
    initAdminPanel();
});

// ==========================================
// 2. GESTIÓN DE EVENTOS
// ==========================================
function setupEventListeners() {
    // Abrir Perfil Propio
    const adjustBtn = getEl('adjustData');
    if (adjustBtn) adjustBtn.onclick = (e) => { 
        e.preventDefault(); 
        loadMyProfile(); 
    };

    // Guardar Datos
    const saveUserBtn = getEl('saveBtnUser');
    const saveAdminBtn = getEl('saveBtnAdmin');
    if (saveUserBtn) saveUserBtn.onclick = (e) => { e.preventDefault(); saveUserData('user'); };
    if (saveAdminBtn) saveAdminBtn.onclick = (e) => { e.preventDefault(); saveUserData('admin'); };

    // Cerrar Modales
    const closeUser = getEl('closeUserModal');
    const closeAdmin = getEl('closeAdminModal');
    if (closeUser) closeUser.onclick = () => closeModalAndReset('modifyUserPopupAdmin');
    if (closeAdmin) closeAdmin.onclick = () => closeModalAndReset('modifyAdminPopup');

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeModalAndReset(overlay.id);
        });
    });

    // Abrir Modal de Contraseña
    const pwdBtns = [getEl('changePwdBtn'), getEl('changePwdBtnAdmin')];
    pwdBtns.forEach(btn => {
        if (btn) btn.onclick = (e) => {
            e.preventDefault();
            fill('verifyCurrentPassword', '');
            toggleModal('verifyPasswordModal', true);
        };
    });

    setupPasswordLogic();
}

function closeModalAndReset(modalId) {
    toggleModal(modalId, false);
    // Al cerrar, nos aseguramos de que los botones vuelvan a apuntar a nosotros
    resetTargetIds();
}

function resetTargetIds() {
    const saveU = getEl('saveBtnUser');
    const saveA = getEl('saveBtnAdmin');
    if (saveU) saveU.setAttribute('data-target-id', appState.myProfileCode);
    if (saveA) saveA.setAttribute('data-target-id', appState.myProfileCode);
}

// ==========================================
// 3. UTILIDADES
// ==========================================
function toggleModal(id, show) {
    const el = getEl(id);
    if (!el) return;
    if (el.tagName === 'DIALOG') {
        show ? (!el.open && el.showModal()) : el.close();
    } else {
        el.style.display = show ? 'flex' : 'none';
    }
}

// ==========================================
// 4. LÓGICA DE PERFIL
// ==========================================
async function loadMyProfile(isInit = false) {
    try {
        const res = await fetch('../../api/GetProfile.php');
        const data = await res.json();

        if (data.success && data.user) {
            const u = data.user;
            const isAdmin = (data.role === 'admin');
            
            // Si es la carga inicial, guardamos nuestro ID permanentemente
            if (isInit) appState.myProfileCode = u.profile_code;

            const prefix = isAdmin ? 'Admin' : 'User';
            const modalId = isAdmin ? 'modifyAdminPopup' : 'modifyUserPopupAdmin';

            fillProfileForm(u, prefix);
            
            const saveBtn = getEl(isAdmin ? 'saveBtnAdmin' : 'saveBtnUser');
            if (saveBtn) saveBtn.setAttribute('data-target-id', u.profile_code);

            if (!isInit) toggleModal(modalId, true);
        }
    } catch (err) { console.error(err); }
}

function fillProfileForm(u, prefix) {
    fill(`firstName${prefix}`, u.name_);
    fill(`lastName${prefix}`, u.surname);
    fill(`email${prefix}`, u.email);
    fill(`username${prefix}`, u.user_name);
    fill(`phone${prefix}`, u.telephone);

    if (prefix === 'Admin') {
        fill('currentAccountAdmin', u.current_account);
    } else {
        fill('cardNumberUser', u.card_no);
        if (u.gender && getEl('genderUser')) getEl('genderUser').value = u.gender;
    }
}

async function saveUserData(role) {
    const suffix = role === 'admin' ? 'Admin' : 'User';
    const saveBtn = getEl(role === 'admin' ? 'saveBtnAdmin' : 'saveBtnUser');
    const targetId = saveBtn?.getAttribute('data-target-id');
    const modalId = role === 'admin' ? 'modifyAdminPopup' : 'modifyUserPopupAdmin';

    const formData = new FormData();
    formData.append('target_id', targetId);
    formData.append('role', role);
    formData.append('name', getEl(`firstName${suffix}`).value);
    formData.append('surname', getEl(`lastName${suffix}`).value);
    formData.append('email', getEl(`email${suffix}`).value);
    formData.append('username', getEl(`username${suffix}`).value);
    formData.append('phone', getEl(`phone${suffix}`).value);

    if (role === 'user') {
        formData.append('cardNumber', getEl('cardNumberUser').value);
        const gender = getEl('genderUser');
        if (gender) formData.append('gender', gender.value);
    } else {
        formData.append('accountNumber', getEl('currentAccountAdmin').value);
    }

    try {
        const res = await fetch('../../api/ModifyUser.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success || data.exito) {
            alert("Datos actualizados correctamente.");
            toggleModal(modalId, false);
            resetTargetIds(); // Volver a apuntar a mi perfil
            if (getEl('adminPanelSection')?.style.display !== 'none') loadUsersTable();
        } else {
            alert("Error: " + data.error);
        }
    } catch (err) { console.error(err); }
}

// ==========================================
// 5. PANEL ADMIN
// ==========================================
async function initAdminPanel() {
    try {
        const res = await fetch('../../api/CheckSession.php');
        const data = await res.json();
        if (data.success && (data.user.role === 'admin' || data.user.isAdmin)) {
            const section = getEl('adminPanelSection');
            if(section) section.style.display = 'flex';
            loadUsersTable();
        }
    } catch (err) { console.error(err); }
}

async function loadUsersTable() {
    const tbody = getEl('adminTableBody');
    const template = getEl('userRowTemplate');
    if (!tbody || !template) return;

    try {
        const res = await fetch('../../api/GetAllUsers.php');
        const data = await res.json();
        const users = data.resultado || data.users || [];
        appState.allUsers = users;
        tbody.innerHTML = '';

        users.forEach((u, index) => {
            const clone = template.content.cloneNode(true);
            clone.querySelector('.col-username').textContent = u.user_name;
            clone.querySelector('.col-fullname').textContent = `${u.name_ || ""} ${u.surname || ""}`;
            clone.querySelector('.col-email').textContent = u.email || "";

            const btnEdit = clone.querySelector('.btn-edit');
            const btnDel = clone.querySelector('.btn-delete');

            if (btnEdit) btnEdit.onclick = () => prepareEditUser(index);
            if (btnDel) btnDel.onclick = () => deleteUser(u.profile_code);

            tbody.appendChild(clone);
        });
    } catch (err) { console.error(err); }
}

function prepareEditUser(index) {
    const u = appState.allUsers[index];
    if (!u) return;
    
    // Forzamos a que se use el modal de 'User' para otros usuarios
    fillProfileForm(u, 'User');
    const saveBtn = getEl('saveBtnUser');
    if (saveBtn) saveBtn.setAttribute('data-target-id', u.profile_code);
    toggleModal('modifyUserPopupAdmin', true);
}

async function deleteUser(id) {
    if (!confirm("¿Estás seguro de eliminar este usuario?")) return;
    try {
        const res = await fetch('../../api/DeleteUser.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        const data = await res.json();
        if (data.success) {
            alert("Usuario eliminado.");
            loadUsersTable();
        } else {
            alert("Error: " + data.error);
        }
    } catch (err) { console.error(err); }
}

// ==========================================
// 6. CONTRASEÑAS
// ==========================================
function setupPasswordLogic() {
    const verifyForm = getEl('verifyPasswordForm');
    const changeForm = getEl('changePasswordForm');

    if (verifyForm) {
        verifyForm.onsubmit = async (e) => {
            e.preventDefault();
            const pass = getEl('verifyCurrentPassword').value;
            // Usamos nuestro propio username para verificar
            const resInit = await fetch('../../api/GetProfile.php');
            const dataInit = await resInit.json();
            const username = dataInit.user.user_name;

            try {
                const res = await fetch('../../api/Login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password: pass })
                });
                const data = await res.json();

                if (data.success) {
                    toggleModal('verifyPasswordModal', false);
                    toggleModal('changePasswordModal', true);
                } else {
                    alert("Contraseña actual incorrecta.");
                }
            } catch (err) { console.error(err); }
        };
    }

    if (changeForm) {
        changeForm.onsubmit = async (e) => {
            e.preventDefault();
            const newP = getEl('newPassword').value;
            const confP = getEl('confirmNewPassword').value;
            
            // El targetId debe ser el del usuario que estamos editando actualmente
            const targetId = getEl('saveBtnUser').getAttribute('data-target-id') || 
                             getEl('saveBtnAdmin').getAttribute('data-target-id');

            if (newP !== confP) return alert("Las contraseñas no coinciden.");
            
            try {
                const res = await fetch('../../api/ModifyPassword.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ profile_code: targetId, password: newP })
                });
                const data = await res.json();
                if (data.success) {
                    alert("Contraseña actualizada con éxito.");
                    toggleModal('changePasswordModal', false);
                    resetTargetIds();
                } else {
                    alert("Error: " + data.error);
                }
            } catch (err) { console.error(err); }
        };
    }
}