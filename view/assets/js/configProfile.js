import { checkSession } from './session.js';
import { loadHeader, loadFooter } from './header.js';
import { apiFetch } from './apiClient.js';

/**
 * Estado de esta pantalla: usuarios cargados y perfil propio.
 *
 * @type {{allUsers: Array<object>, myProfileCode: number|null}}
 */
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
    if (!isLogged) return window.location.replace('login.html');

    await loadHeader('configProfile');
    await loadFooter();

    await loadMyProfile(true);
    setupEventListeners();
    initAdminPanel();
});

/**
 * Conecta botones de editar, borrar y cambiar contrasena.
 *
 * @returns {void}
 */
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

    const deleteBtn = getEl('deleteBtn');
    if (deleteBtn) {
        deleteBtn.onclick = (e) => {
            e.preventDefault();

            // Puede venir del perfil propio o del modal abierto por el admin.
            const targetId = appState.myProfileCode ||
                getEl('saveBtnUser')?.getAttribute('data-target-id') ||
                getEl('saveBtnAdmin')?.getAttribute('data-target-id');

            if (targetId) {
                deleteUser(targetId);
            } else {
                alert("Error: No se ha podido identificar tu usuario para eliminarlo.");
                console.error("Error: targetId es null/undefined");
            }
        };
    }

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

/**
 * Cierra un modal y vuelve a apuntar al perfil propio.
 *
 * @param {string} modalId ID del modal.
 * @returns {void}
 */
function closeModalAndReset(modalId) {
    toggleModal(modalId, false);
    resetTargetIds();
}

/**
 * Evita que quede seleccionado otro usuario despues de cerrar un modal.
 *
 * @returns {void}
 */
function resetTargetIds() {
    const saveU = getEl('saveBtnUser');
    const saveA = getEl('saveBtnAdmin');
    if (saveU) saveU.setAttribute('data-target-id', appState.myProfileCode);
    if (saveA) saveA.setAttribute('data-target-id', appState.myProfileCode);
}

/**
 * Muestra u oculta los modales hechos con div.
 *
 * @param {string} id ID del elemento modal.
 * @param {boolean} show Indica si debe mostrarse.
 * @returns {void}
 */
function toggleModal(id, show) {
    const el = getEl(id);
    if (!el) return;
    el.style.display = show ? 'flex' : 'none';
}

/**
 * Carga el perfil actual y rellena el formulario de user o admin.
 *
 * @param {boolean} [isInit=false] Si es true, solo inicializa estado sin abrir modal.
 * @returns {Promise<void>}
 */
async function loadMyProfile(isInit = false) {
    try {
        const data = await apiFetch('../../api/GetProfile.php', { credentials: 'include' });
        console.log("Respuesta GetProfile:", data);

        if (data.status === 'success' && data.data && data.data.user) {
            const u = data.data.user;
            const isAdmin = (data.data.role === 'admin');

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

/**
 * Rellena campos que comparten nombre y cambian por sufijo User/Admin.
 *
 * @param {object} u Perfil recibido desde API.
 * @param {'User'|'Admin'} prefix Sufijo de los campos del formulario.
 * @returns {void}
 */
function fillProfileForm(u, prefix) {
    fill(`firstName${prefix}`, u.name_);
    fill(`lastName${prefix}`, u.surname);
    fill(`email${prefix}`, u.email);
    fill(`username${prefix}`, u.user_name);
    fill(`phone${prefix}`, u.telephone);

    if (prefix === 'Admin') {
        fill('currentAccountAdmin', u.current_account);
    } else {
        fill('cardNumberUser', '');
        if (u.gender && getEl('genderUser')) getEl('genderUser').value = u.gender;
        fill('directionUser', u.direction);
    }
}

/**
 * Valida el formulario y envia cambios segun el rol editado.
 *
 * @param {'user'|'admin'} role Tipo de perfil editado.
 * @returns {Promise<void>}
 */
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

        // Si se deja vacia, el servidor conserva la tarjeta existente.
        if (cardNumberClean !== "") {
            formData.append('cardNumber', cardNumberClean);
        }

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
        const data = await apiFetch('../../api/ModifyUser.php', { method: 'POST', body: formData, credentials: 'include' });
        console.log("Respuesta ModifyUser:", data);

        alert("Datos actualizados correctamente.");
        toggleModal(modalId, false);
        resetTargetIds();

        if (getEl('adminPanelSection')?.style.display !== 'none') {
            loadUsersTable();
        }
    } catch (err) {
        console.error("Error API:", err);
        alert(err.message || "Error de conexión.");
    }
}

/**
 * Muestra la tabla de usuarios solo si la sesion es admin.
 *
 * @returns {Promise<void>}
 */
async function initAdminPanel() {
    try {
        const data = await apiFetch('../../api/CheckSession.php', { credentials: 'include' });
        console.log("Respuesta CheckSession (Admin Panel):", data);
        if (data.status === 'success' && data.data && data.data.user && data.data.user.role === 'admin') {
            const section = getEl('adminPanelSection');
            if (section) section.style.display = 'flex';
            loadUsersTable();
        }
    } catch (err) { console.error(err); }
}

/**
 * Carga usuarios en la tabla y conecta editar/borrar por fila.
 *
 * @returns {Promise<void>}
 */
async function loadUsersTable() {
    const tbody = getEl('adminTableBody');
    const template = getEl('userRowTemplate');
    if (!tbody || !template) return;

    try {
        const data = await apiFetch('../../api/GetAllUsers.php', { credentials: 'include' });
        console.log("Respuesta GetAllUsers:", data);

        const users = data.status === 'success' && Array.isArray(data.data) ? data.data : [];

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

/**
 * Abre el modal con el usuario guardado en appState.allUsers.
 *
 * @param {number} index Indice de usuario en cache.
 * @returns {void}
 */
function prepareEditUser(index) {
    const u = appState.allUsers[index];
    if (!u) return;

    fillProfileForm(u, 'User');
    getEl('saveBtnUser').setAttribute('data-target-id', u.profile_code);
    toggleModal('modifyUserPopupAdmin', true);
}

/**
 * Pide borrar un perfil; la API decide si el usuario tiene permiso.
 *
 * @param {number|string} id Codigo de perfil objetivo.
 * @returns {Promise<void>}
 */
async function deleteUser(id) {
    if (!confirm("¿Eliminar usuario?")) return;
    try {
        const data = await apiFetch('../../api/DeleteUser.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id }),
            credentials: 'include'
        });
        console.log("Respuesta DeleteUser:", data);

        alert("Usuario eliminado.");
        loadUsersTable();
    } catch (err) {
        console.error(err);
        alert(err.message || "No se pudo eliminar.");
    }
}

/**
 * Cambio de contrasena en dos pasos: comprobar actual y guardar nueva.
 *
 * @returns {void}
 */
function setupPasswordLogic() {
    const verifyForm = getEl('verifyPasswordForm');
    const changeForm = getEl('changePasswordForm');

    const closePassBtns = document.querySelectorAll('.close-pass-btn, dialog .deleteBtn');

    closePassBtns.forEach(btn => {
        btn.onclick = (e) => {
            const dialog = btn.closest('dialog');
            if (dialog && dialog.open) {
                if (typeof dialog.close === "function") {
                    dialog.close();
                } else {
                    toggleModal(dialog.id, false);
                }
            }
        };
    });

    if (verifyForm) {
        verifyForm.onsubmit = async (e) => {
            e.preventDefault();
            const pass = getEl('verifyCurrentPassword').value;


            try {
                const dataInit = await apiFetch('../../api/GetProfile.php', { credentials: 'include' });
                const username = dataInit.data.user.user_name;

                const data = await apiFetch('../../api/Login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password: pass }),
                    credentials: 'include'
                });
                console.log("Respuesta VerifyPassword (Login API):", data);

                const verifyModal = getEl('verifyPasswordModal');
                if (verifyModal && typeof verifyModal.close === 'function') verifyModal.close();
                else toggleModal('verifyPasswordModal', false);


                const changeModal = getEl('changePasswordModal');
                if (changeModal) {

                    if (changeForm) changeForm.reset();

                    if (typeof changeModal.showModal === 'function') changeModal.showModal();
                    else toggleModal('changePasswordModal', true);
                }
            } catch (err) {
                console.error(err);
                alert("Contraseña actual incorrecta.");
                getEl('verifyCurrentPassword').value = '';
            }
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
                const data = await apiFetch('../../api/ModifyPassword.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ profile_code: targetId, password: newP }),
                    credentials: 'include'
                });
                console.log("Respuesta ModifyPassword:", data);
                alert("Contraseña actualizada con éxito.");

                const changeModal = getEl('changePasswordModal');
                if (changeModal && typeof changeModal.close === 'function') changeModal.close();
                else toggleModal('changePasswordModal', false);

                resetTargetIds();
            } catch (err) {
                console.error(err);
                alert("Error: " + (err.message || "No se pudo actualizar la contraseña."));
            }
        };
    }
}
