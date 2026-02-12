import { checkSession } from './session.js';
import { loadHeader, loadFooter } from './header.js';

const appState = {
    allUsers: [],
    myProfileCode: null
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

    // configuracion para eliminar usuarios solo el admin puede ver el botón
    const deleteBtn = getEl('deleteBtn');
    if (deleteBtn) {
        deleteBtn.onclick = (e) => {
            e.preventDefault();
            // Obtenemos el ID del usuario que se está editando actualmente
            const targetId = getEl('saveBtnUser').getAttribute('data-target-id');
            if (targetId) {
                deleteUser(targetId);
            } else {
                console.error("Error: No se encontró el ID del usuario.");
            }
        };
    }

    // logica para cambiar la contraseña
    const changePwdBtn = getEl('changePwdBtn');
    const changePwdBtnAdmin = getEl('changePwdBtnAdmin');

    const openPasswordModal = (e) => {
        e.preventDefault();

        const verifyForm = getEl('verifyPasswordForm');
        if (verifyForm) verifyForm.reset();

        const dialog = getEl('verifyPasswordModal');
        if (dialog) {

            if (typeof dialog.showModal === "function") {
                dialog.showModal();
            } else {
                toggleModal('verifyPasswordModal', true);
            }
        }
    };

    if (changePwdBtn) changePwdBtn.onclick = openPasswordModal;
    if (changePwdBtnAdmin) changePwdBtnAdmin.onclick = openPasswordModal;

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
        console.log("Status GetProfile:", res.status);
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
    const formId = role === 'admin' ? 'profileFormAdmin' : 'profileFormUser';
    const form = document.getElementById(formId);
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const phoneRaw = getEl(`phone${suffix}`).value.trim();
    const name = getEl(`firstName${suffix}`).value.trim();
    const surname = getEl(`lastName${suffix}`).value.trim();
    const email = getEl(`email${suffix}`).value.trim();
    const username = getEl(`username${suffix}`).value.trim();

    if (email.length > 0) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert("El formato del email no es válido.");
            return;
        }
    }

    let phoneClean = "";
    if (phoneRaw.length > 0) {
        phoneClean = phoneRaw.replace(/[\s-]/g, '');
        // Validamos que sean 9 dígitos
        if (!/^\d{9}$/.test(phoneClean)) {
            alert("El teléfono debe tener 9 dígitos numéricos.");
            return;
        }
    }

    const formData = new FormData();
    formData.append('target_id', targetId);
    formData.append('role', role);
    formData.append('name', name);
    formData.append('surname', surname);
    formData.append('email', email);
    formData.append('username', username);
    formData.append('phone', phoneClean);

    if (role === 'user') {
        const cardNumberRaw = getEl('cardNumberUser').value.trim();
        let cardNumberClean = "";

        if (cardNumberRaw.length > 0) {
            cardNumberClean = cardNumberRaw.replace(/[-\s]/g, '');
            if (!/^\d{16}$/.test(cardNumberClean)) {
                alert("La tarjeta debe tener 16 dígitos.");
                return;
            }
        }

        //el servidor recive el numero limpito
        formData.append('cardNumber', cardNumberClean);

        const gender = getEl('genderUser');
        if (gender) formData.append('gender', gender.value);

        const direction = getEl('directionUser').value.trim();
        if (direction.length > 255) {
            alert("La dirección es demasiado larga (máx 255).");
            return;
        }
        formData.append('direction', direction);

    } else {
        const accountNumberRaw = getEl('currentAccountAdmin').value.trim();
        let accountNumberClean = "";

        if (accountNumberRaw.length > 0) {
            accountNumberClean = accountNumberRaw.replace(/[-\s]/g, '');
            if (accountNumberClean.length !== 24) {
                alert("La cuenta bancaria debe tener 24 caracteres.");
                return;
            }
        }
        formData.append('accountNumber', accountNumberClean);
    }

    try {
        const res = await fetch('../../api/ModifyUser.php', { method: 'POST', body: formData });
        console.log("Status ModifyUser:", res.status);
        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error("Respuesta servidor:", text);
            alert("Error técnico en el servidor.");
            return;
        }

        if (data.success) {
            alert("Datos actualizados correctamente.");
            toggleModal(modalId, false);
            resetTargetIds();

            if (getEl('adminPanelSection')?.style.display !== 'none') {
                loadUsersTable();
            }
        } else {
            alert("Error: " + data.error);
        }
    } catch (err) {
        console.error("Error API:", err);
        alert("Error de conexión.");
    }
}

async function initAdminPanel() {
    try {
        const res = await fetch('../../api/CheckSession.php');
        console.log("Status CheckSession (Admin Panel):", res.status);
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
        console.log("Status GetAllUsers:", res.status);
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
        console.log("Status DeleteUser:", res.status);
        let data;
        const contentType = res.headers.get("content-type");
        if (contentType && contentType.includes("application/json")) {
            data = await res.json();
        } else {
            const text = await res.text();
            try { data = JSON.parse(text); } catch (e) { console.error(text); }
        }

        if (data && data.success) {
            if (data.isSelfDelete) {
                alert("Tu cuenta ha sido eliminada correctamente.");
                window.location.href = 'login.html';
                return;
            }
            alert("Usuario eliminado.");
            loadUsersTable();
        } else {
            alert("Error: " + data.error);
        }
    } catch (err) { console.error(err); }
}

function setupPasswordLogic() {
    const verifyForm = getEl('verifyPasswordForm');
    const changeForm = getEl('changePasswordForm');

    const closePassBtns = document.querySelectorAll('.close-pass-btn, button[command="close"]');
    closePassBtns.forEach(btn => {
        btn.onclick = (e) => {
            const dialog = btn.closest('dialog');
            if (dialog && typeof dialog.close === "function") dialog.close();
            else if (dialog) dialog.style.display = 'none';
        };
    });

    if (verifyForm) {
        verifyForm.onsubmit = async (e) => {
            e.preventDefault();
            const pass = getEl('verifyCurrentPassword').value;


            const resInit = await fetch('../../api/GetProfile.php');
            const dataInit = await resInit.json();
            const username = dataInit.user.user_name;

            try {
                const res = await fetch('../../api/Login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password: pass })
                });
                console.log("Status VerifyPassword (Login API):", res.status);
                const data = await res.json();

                if (data.success) {

                    const verifyModal = getEl('verifyPasswordModal');
                    if (verifyModal && typeof verifyModal.close === 'function') verifyModal.close();
                    else toggleModal('verifyPasswordModal', false);


                    const changeModal = getEl('changePasswordModal');
                    if (changeModal) {

                        if (changeForm) changeForm.reset();

                        if (typeof changeModal.showModal === 'function') changeModal.showModal();
                        else toggleModal('changePasswordModal', true);
                    }

                } else {
                    alert("Contraseña actual incorrecta.");
                    getEl('verifyCurrentPassword').value = '';
                }
            } catch (err) { console.error(err); }
        };
    }

    if (changeForm) {
        changeForm.onsubmit = async (e) => {
            e.preventDefault();
            const newP = getEl('newPassword').value;
            const confP = getEl('confirmNewPassword').value;


            const targetId = getEl('saveBtnUser').getAttribute('data-target-id') ||
                getEl('saveBtnAdmin').getAttribute('data-target-id');
            if (newP.length < 4) return alert("La contraseña debe tener al menos 4 caracteres.");
            if (newP !== confP) return alert("Las contraseñas no coinciden.");

            try {
                const res = await fetch('../../api/ModifyPassword.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ profile_code: targetId, password: newP })
                });
                console.log("Status ModifyPassword:", res.status);
                const data = await res.json();
                if (data.success) {
                    alert("Contraseña actualizada con éxito.");

                    const changeModal = getEl('changePasswordModal');
                    if (changeModal && typeof changeModal.close === 'function') changeModal.close();
                    else toggleModal('changePasswordModal', false);

                    resetTargetIds();
                } else {
                    alert("Error: " + data.error);
                }
            } catch (err) { console.error(err); }
        };
    }
}