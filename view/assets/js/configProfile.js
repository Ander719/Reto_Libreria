import { checkSession } from './session.js';
import { loadHeader, loadFooter } from './header.js';

const appState = {
    allUsers: [],
    myProfileCode: null // Almacena el ID del usuario logueado
};

const getEl = (id) => document.getElementById(id);

const fill = (id, value) => {
    const el = getEl(id);
    if (el) el.value = (value === null || value === undefined) ? '' : value;
};

document.addEventListener('DOMContentLoaded', async () => {
    const isLogged = await checkSession();
    if (!isLogged) return window.location.href = 'login.html';

    await loadHeader('configProfile');
    await loadFooter();

    await loadMyProfile(true);
    setupEventListeners();
    initAdminPanel();
});

function setupEventListeners() {
    const adjustBtn = getEl('adjustData');
    if (adjustBtn) adjustBtn.onclick = (e) => {
        e.preventDefault();
        loadMyProfile();
    };

    const saveUserBtn = getEl('saveBtnUser');
    const saveAdminBtn = getEl('saveBtnAdmin');
    if (saveUserBtn) saveUserBtn.onclick = (e) => { e.preventDefault(); saveUserData('user'); };
    if (saveAdminBtn) saveAdminBtn.onclick = (e) => { e.preventDefault(); saveUserData('admin'); };

    const closeUser = getEl('closeUserModal');
    const closeAdmin = getEl('closeAdminModal');
    if (closeUser) closeUser.onclick = () => closeModalAndReset('modifyUserPopupAdmin');
    if (closeAdmin) closeAdmin.onclick = () => closeModalAndReset('modifyAdminPopup');

    setupPasswordLogic();
}

function closeModalAndReset(modalId) {
    toggleModal(modalId, false);
    resetTargetIds();
}

function resetTargetIds() {
    const saveU = getEl('saveBtnUser');
    const saveA = getEl('saveBtnAdmin');
    if (saveU) saveU.setAttribute('data-target-id', appState.myProfileCode);
    if (saveA) saveA.setAttribute('data-target-id', appState.myProfileCode);
}

function toggleModal(id, show) {
    const el = getEl(id);
    if (!el) return;
    el.style.display = show ? 'flex' : 'none';
}

async function loadMyProfile(isInit = false) {
    try {
        const res = await fetch('../../api/GetProfile.php');
        const data = await res.json();

        if (data.success && data.user) {
            const u = data.user;
            const isAdmin = (data.role === 'admin');

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
        fill('directionUser', u.direction);
    }
}

async function saveUserData(role) {
    const suffix = role === 'admin' ? 'Admin' : 'User';
    const saveBtn = getEl(role === 'admin' ? 'saveBtnAdmin' : 'saveBtnUser');
    const targetId = saveBtn?.getAttribute('data-target-id');
    const modalId = role === 'admin' ? 'modifyAdminPopup' : 'modifyUserPopupAdmin';

    // Captura y limpieza de valores básicos
    const phone = getEl(`phone${suffix}`).value.trim();
    const name = getEl(`firstName${suffix}`).value.trim();
    const surname = getEl(`lastName${suffix}`).value.trim();
    const email = getEl(`email${suffix}`).value.trim();
    const username = getEl(`username${suffix}`).value.trim();

    // 1. RESTRICCIÓN: Teléfono (9 números exactos)
    if (phone.length !== 9 || isNaN(phone)) {
        alert("El teléfono debe tener exactamente 9 números.");
        return;
    }

    const formData = new FormData();
    formData.append('target_id', targetId);
    formData.append('role', role);
    formData.append('name', name);
    formData.append('surname', surname);
    formData.append('email', email);
    formData.append('username', username);
    formData.append('phone', phone);

    if (role === 'user') {
        const cardNumberRaw = getEl('cardNumberUser').value.trim();

        // 2. RESTRICCIÓN: Limpiar guiones de la tarjeta para validar 16 dígitos
        // Esto permite formatos como 1234-5678-1234-5678
        const cardNumberClean = cardNumberRaw.replace(/-/g, '');

        if (cardNumberClean.length !== 16 || isNaN(cardNumberClean)) {
            alert("El número de tarjeta debe tener exactamente 16 números (los guiones no cuentan).");
            return;
        }

        // Enviamos el número limpio al servidor
        formData.append('cardNumber', cardNumberClean);

        const gender = getEl('genderUser');
        if (gender) formData.append('gender', gender.value);

        const direction = getEl('directionUser').value.trim();
        if(direction.length > 255) {
            alert("La dirección debe tener como máximo 255 caracteres.");
            return;
        }
        formData.append('direction', direction);

    } else {
        const accountNumberRaw = getEl('currentAccountAdmin').value.trim();
        // Limpiar guiones y espacios de la cuenta bancaria (IBAN)
        const accountNumberClean = accountNumberRaw.replace(/[-\s]/g, '');

        // 2. Validación de Cuenta Bancaria (24 caracteres reales)
        if (accountNumberClean.length !== 24) {
            alert("La cuenta bancaria debe tener exactamente 24 caracteres (los guiones o espacios no cuentan).");
            return;
        }
        formData.append('accountNumber', accountNumberClean);
    }

    try {
        const res = await fetch('../../api/ModifyUser.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            alert("Datos actualizados correctamente.");
            toggleModal(modalId, false);
            resetTargetIds(); // Restablece los IDs a tu propio perfil

            // Si el panel de administrador está visible, refrescamos la lista de usuarios
            if (getEl('adminPanelSection')?.style.display !== 'none') {
                loadUsersTable();
            }
        } else {
            alert("Error: " + data.error);
        }
    } catch (err) {
        console.error("Error en la petición:", err);
        alert("Error de conexión con el servidor.");
    }
}

async function initAdminPanel() {
    try {
        const res = await fetch('../../api/CheckSession.php');
        const data = await res.json();
        if (data.success && data.user.role === 'admin') {
            const section = getEl('adminPanelSection');
            if (section) section.style.display = 'flex';
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

        // CORRECCIÓN: La API puede devolver el array directamente o bajo la clave 'users'
        const users = Array.isArray(data) ? data : (data.users || data.resultado || []);

        appState.allUsers = users;
        tbody.innerHTML = '';

        if (users.length === 0) {
            console.warn("No se encontraron usuarios o el formato de respuesta es incorrecto.");
            return;
        }

        users.forEach((u, index) => {
            const clone = template.content.cloneNode(true);

            // Asegúrate de usar 'u.name_' que es como viene de la base de datos
            clone.querySelector('.col-username').textContent = u.user_name || "N/A";
            clone.querySelector('.col-fullname').textContent = `${u.name_ || ""} ${u.surname || ""}`.trim() || "Sin nombre";
            clone.querySelector('.col-email').textContent = u.email || "Sin email";

            const btnEdit = clone.querySelector('.btn-edit');
            const btnDel = clone.querySelector('.btn-delete');

            if (btnEdit) btnEdit.onclick = () => prepareEditUser(index);
            if (btnDel) btnDel.onclick = () => deleteUser(u.profile_code);

            tbody.appendChild(clone);
        });
    } catch (err) {
        console.error("Error cargando la tabla de usuarios:", err);
    }
}

function prepareEditUser(index) {
    const u = appState.allUsers[index];
    if (!u) return;

    fillProfileForm(u, 'User');
    getEl('saveBtnUser').setAttribute('data-target-id', u.profile_code);
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