import { checkSession } from './session.js';
import { loadHeader, loadFooter } from './header.js';

const appState = { allUsers: [] };
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
    setupEventListeners();
    initAdminPanel();
});

// ==========================================
// 2. GESTIÓN DE EVENTOS
// ==========================================
function setupEventListeners() {
    // Abrir Perfil Propio
    const adjustBtn = getEl('adjustData');
    if (adjustBtn) adjustBtn.onclick = (e) => { e.preventDefault(); loadMyProfile(); };

    // Guardar Datos
    const saveUserBtn = getEl('saveBtnUser');
    const saveAdminBtn = getEl('saveBtnAdmin');
    if (saveUserBtn) saveUserBtn.onclick = (e) => { e.preventDefault(); saveUserData('user'); };
    if (saveAdminBtn) saveAdminBtn.onclick = (e) => { e.preventDefault(); saveUserData('admin'); };

    // Cerrar Modales DIV (Los Dialogs se cierran solos con command="close")
    const closeUser = getEl('closeUserModal');
    const closeAdmin = getEl('closeAdminModal');
    if (closeUser) closeUser.onclick = () => toggleModal('modifyUserPopupAdmin', false);
    if (closeAdmin) closeAdmin.onclick = () => toggleModal('modifyAdminPopup', false);

    // Click en el fondo oscuro de los DIVs para cerrar
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            // Si el clic fue directamente en el fondo (overlay) y no en la tarjeta
            if (e.target === overlay) {
                overlay.style.display = 'none';
            }
        });
    });

    // Abrir Modal de Contraseña (Dialog)
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

// ==========================================
// 3. FUNCIÓN INTELIGENTE TOGGLE MODAL
// ==========================================
function toggleModal(id, show) {
    const el = getEl(id);
    if (!el) return;

    // Detectamos si es un DIALOG nativo o un DIV clásico
    if (el.tagName === 'DIALOG') {
        if (show) {
            if (!el.open) el.showModal();
        } else {
            el.close();
        }
    } else {
        // Es un DIV
        el.style.display = show ? 'flex' : 'none';
    }
}

// ==========================================
// 4. LOGICA DE PERFIL
// ==========================================
async function loadMyProfile() {
    try {
        const res = await fetch('../../api/GetProfile.php');
        const data = await res.json();

        if (data.success && data.user) {
            const u = data.user;
            //console.log(u);
            const isAdmin = (data.role === 'admin');
            const prefix = isAdmin ? 'Admin' : 'User';
            const modalId = isAdmin ? 'modifyAdminPopup' : 'modifyUserPopupAdmin';

            fillProfileForm(u, prefix);
            
            const saveBtn = getEl(isAdmin ? 'saveBtnAdmin' : 'saveBtnUser');
            if (saveBtn) saveBtn.setAttribute('data-target-id', u.profile_code);

            toggleModal(modalId, true);
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
        fill('profileCodeAdmin', u.profile_code);
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

        if (data.exito || data.success) {
            alert("Datos actualizados.");
            toggleModal(modalId, false);
            
            if (getEl('adminPanelSection').style.display !== 'none') {
                loadUsersTable();
            }
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
            clone.querySelector('.col-fullname').textContent = (u.name_ && u.surname) ? `${u.name_} ${u.surname}` : "";
            clone.querySelector('.col-email').textContent = u.email ? u.email : "";

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
    fillProfileForm(u, 'User');
    const saveBtn = getEl('saveBtnUser');
    if (saveBtn) saveBtn.setAttribute('data-target-id', u.profile_code);
    toggleModal('modifyUserPopupAdmin', true);
}

async function deleteUser(id) {
    if (!confirm("¿Eliminar usuario?")) return;
    try {
        const res = await fetch('../../api/DeleteUser.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        let rawtext = await res.text();
        console.log("Raw response:", rawtext);
        const data = await JSON.parse(rawtext);
        if (data.success) {
            alert("Usuario eliminado.");
            loadUsersTable();
        }else {
            console.error("Error deleting user:", data.error);
        }
    } catch (err) { console.error(err); }
}

// ==========================================
// 6. CONTRASEÑAS (DIALOGS)
// ==========================================
function setupPasswordLogic() {
    const verifyForm = getEl('verifyPasswordForm');
    const changeForm = getEl('changePasswordForm');

    if (verifyForm) {
        verifyForm.onsubmit = async (e) => {
            e.preventDefault();
            const pass = getEl('verifyCurrentPassword').value;
            const username = getEl('usernameUser')?.value || getEl('usernameAdmin')?.value;

            try {
                const res = await fetch('../../api/Login.php', {
                    method: 'POST',
                    body: JSON.stringify({ username, password: pass })
                });
                const data = await res.json();

                if (data.success || data.exito) {
                    toggleModal('verifyPasswordModal', false);
                    fill('newPassword', '');
                    fill('confirmNewPassword', '');
                    toggleModal('changePasswordModal', true);
                } else {
                    alert("Contraseña incorrecta.");
                }
            } catch (err) { console.error(err); }
        };
    }

    if (changeForm) {
        changeForm.onsubmit = async (e) => {
            e.preventDefault();
            const newP = getEl('newPassword').value;
            const confP = getEl('confirmNewPassword').value;
            const targetId = getEl('saveBtnUser')?.getAttribute('data-target-id') || getEl('saveBtnAdmin')?.getAttribute('data-target-id');

            if (newP !== confP) return alert("No coinciden.");
            
            try {
                const res = await fetch('../../api/ModifyPassword.php', {
                    method: 'POST',
                    body: JSON.stringify({ profile_code: targetId, password: newP })
                });
                const data = await res.json();
                if (data.success) {
                    alert("Contraseña cambiada.");
                    toggleModal('changePasswordModal', false);
                }
            } catch (err) { console.error(err); }
        };
    }
}