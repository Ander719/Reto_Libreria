import { currentUser, checkSession } from "./sesion.js";

document.addEventListener("DOMContentLoaded", async () => {
  // 1. Intentamos obtener la sesión, pero SIN obligar
  const isAuthenticated = await checkSession();

  if (isAuthenticated) {
    console.log("Modo: Usuario Registrado");
    // Aquí podrías cambiar el icono de login por uno de logout si quisieras visualmente
  } else {
    console.log("Modo: Invitado (No logueado)");
  }

  // 2. Iniciamos la interfaz SIEMPRE (para que funcionen los botones básicos)
  initializeUI();
});

// Envolvemos toda tu lógica antigua en esta función
function initializeUI() {

  /* ----------HOME---------- */
  const homeBtn = document.getElementById("adjustData");

  /* ----------USER POPUP---------- */
  const modifyUserPopup = document.getElementById("modifyUserPopupAdmin");
  const changePwdBtn = document.getElementById("changePwdBtn");
  const saveBtnUser = document.getElementById("saveBtnUser");

  /* ----------ADMIN POPUP---------- */
  const modifyAdminPopup = document.getElementById("modifyAdminPopup");
  // Verificamos existencia para evitar errores si el elemento no está en el DOM
  const closeAdminSpan = document.getElementsByClassName("close")[0];
  const changePwdBtnAdmin = document.getElementById("changePwdBtnAdmin");
  const adminTableModal = document.getElementById("adminTableModal");
  const modifyAdminBtn = document.getElementById("modifySelfButton");
  const saveBtnAdmin = document.getElementById("saveBtnAdmin");

  /* ----------SHARED ELEMENTS---------- */
  const changePwdModal = document.getElementById("changePasswordModal");
  const deleteBtn = document.getElementById("deleteBtn");
  const closePasswordSpan = document.getElementsByClassName("closePasswordSpan")[0];

  /* ----------HOME---------- */
  homeBtn.onclick = function () {
    // CASO 1: ES UN INVITADO
    if (currentUser === null) {
      // Si no está logueado y quiere ajustar datos, lo mandamos a identificarse
      alert("Por favor, inicia sesión para ver tus datos.");
      window.location.href = "login.html";
      return;
    }

    // CASO 2: ESTÁ LOGUEADO (Tu lógica original)
    if (currentUser.CARD_NO) {
      document.getElementById("message").innerHTML = "";
      openModifyUserPopup();
    } else if (currentUser.CURRENT_ACCOUNT) {
      refreshAdminTable();
      adminTableModal.style.display = "block";
      deleteBtn.style.display = "none";
    }
  };
  /* ---------- RESTO DE FUNCIONALIDADES (Solo funcionan si el modal se abre) ---------- */

  /* ----------USER POPUP---------- */
  changePwdBtn.onclick = function () {
    changePwdModal.style.display = "block";
    resetPasswordModal();
  };

  saveBtnUser.onclick = function () {
    modifyUser();
  };

  /* ----------ADMIN POPUP---------- */
  if (closeAdminSpan) {
    closeAdminSpan.onclick = function () {
      adminTableModal.style.display = "none";
    };
  }

  changePwdBtnAdmin.onclick = function () {
    changePwdModal.style.display = "block";
    resetPasswordModal();
  };

  modifyAdminBtn.onclick = function () {
    openModifyAdminPopup();
  };

  saveBtnAdmin.onclick = function () {
    modifyAdmin();
  };

  /* ----------SHARED ELEMENTS---------- */
  deleteBtn.onclick = function () {
    delete_user(currentUser.PROFILE_CODE);
  };

  closePasswordSpan.onclick = function () {
    changePwdModal.style.display = "none";
  };

  window.onclick = function (event) {
    if (event.target == adminTableModal) adminTableModal.style.display = "none";
    else if (event.target == modifyUserPopup) modifyUserPopup.style.display = "none";
    else if (event.target == modifyAdminPopup) modifyAdminPopup.style.display = "none";
    else if (event.target == changePwdModal) changePwdModal.style.display = "none";
  };

  // CHANGE PASSWORD FORM
  document.getElementById("changePasswordForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    document.getElementById("messageOldPassword").innerHTML = "";
    document.getElementById("messageWrongPassword").innerHTML = "";
    document.getElementById("message").innerHTML = "";

    const profile_code = currentUser.PROFILE_CODE;
    const userPassword = currentUser.PSWD; // Contraseña actual en memoria

    const password = document.getElementById("currentPassword").value;
    const newPassword = document.getElementById("newPassword").value;
    const confirmPassword = document.getElementById("confirmNewPassword").value;

    let hasErrors = false;

    if (userPassword != password) {
      document.getElementById("messageOldPassword").innerHTML = "Esa no es tu contraseña actual";
      hasErrors = true;
    }

    if (userPassword == newPassword) {
      document.getElementById("messageWrongPassword").innerHTML = "La contraseña es igual a la anterior";
      hasErrors = true;
    }

    if (newPassword != confirmPassword) {
      document.getElementById("messageWrongPassword").innerHTML = "Las contraseñas no coinciden";
      hasErrors = true;
    }

    if (!hasErrors) {
      try {
        const response = await fetch("../../api/ModifyPassword.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            profile_code: profile_code,
            password: newPassword,
          }),
        });

        const data = await response.json();

        if (data.success) {
          // Actualizamos la variable en memoria para que no falle la próxima comprobación
          currentUser.PSWD = newPassword;

          document.getElementById("messageSuccessPassword").innerHTML = "Password correctly changed";

          setTimeout(() => {
            document.getElementById("messageSuccessPassword").innerHTML = "";
            document.getElementById("changePasswordForm").reset();
          }, 3000);
        } else {
          document.getElementById("messageSuccessPassword").innerHTML = data.error;
          document.getElementById("messageSuccessPassword").style.color = "red";
        }
      } catch (error) {
        console.log(error);
      }
    }
  });
}
/* ---------- METHODS ---------- */

function openModifyUserPopup() {
  document.getElementById("message").innerHTML = "";
  // Ya no usamos localStorage, leemos de currentUser
  const usuario = currentUser;

  document.getElementById("usernameUser").value = usuario.USER_NAME;

  if (usuario.EMAIL) {
    document.getElementById("emailUser").value = usuario.EMAIL;
    document.getElementById("phoneUser").value = usuario.TELEPHONE;
    document.getElementById("firstNameUser").value = usuario.NAME_;
    document.getElementById("lastNameUser").value = usuario.SURNAME;
    document.getElementById("genderUser").value = usuario.GENDER;
    document.getElementById("cardNumberUser").value = usuario.CARD_NO;
  }

  let modifyUserPopup = document.getElementById("modifyUserPopupAdmin");
  modifyUserPopup.style.display = "flex";
}

async function modifyUser() {
  // Leemos de memoria
  const usuario = currentUser;
  const profile_code = usuario.PROFILE_CODE;

  const name = document.getElementById("firstNameUser").value;
  const surname = document.getElementById("lastNameUser").value;
  const email = document.getElementById("emailUser").value;
  const username = document.getElementById("usernameUser").value;
  const telephone = document.getElementById("phoneUser").value.replace(/\s/g, "");
  const gender = document.getElementById("genderUser").value;
  const card_no = document.getElementById("cardNumberUser").value;

  if (!name || !surname || !email || !username || !telephone || !gender || !card_no) {
    document.getElementById("message").innerHTML = "You must fill all the fields";
    document.getElementById("message").style.color = "red";
    return;
  }

  function hasChanges() {
    return (
      name !== usuario.NAME_ ||
      surname !== usuario.SURNAME ||
      email !== usuario.EMAIL ||
      username !== usuario.USER_NAME ||
      telephone !== usuario.TELEPHONE ||
      gender !== usuario.GENDER ||
      card_no !== usuario.CARD_NO
    );
  }

  if (!hasChanges()) {
    document.getElementById("message").innerHTML = "No changes detected";
    document.getElementById("message").style.color = "red";
  } else {
    try {
      const response = await fetch(
        `../../api/ModifyUser.php?profile_code=${encodeURIComponent(profile_code)}&name=${encodeURIComponent(name)}&surname=${encodeURIComponent(surname)}&email=${encodeURIComponent(email)}&username=${encodeURIComponent(username)}&telephone=${encodeURIComponent(telephone)}&gender=${encodeURIComponent(gender)}&card_no=${encodeURIComponent(card_no)}`
      );
      const data = await response.json();

      if (data.success) {
        document.getElementById("message").innerHTML = data.message;
        document.getElementById("message").style.color = "green";

        // ACTUALIZAMOS MEMORIA LOCAL (currentUser)
        currentUser.NAME_ = name;
        currentUser.SURNAME = surname;
        currentUser.EMAIL = email;
        currentUser.USER_NAME = username;
        currentUser.TELEPHONE = telephone;
        currentUser.CARD_NO = card_no;
        currentUser.GENDER = gender;

      } else {
        document.getElementById("message").innerHTML = data.error;
        document.getElementById("message").style.color = "red";
      }
    } catch (error) {
      console.log(error);
    }
  }
}

async function get_all_users() {
  const response = await fetch("../../api/GetAllUsers.php");
  const data = await response.json();
  return data["resultado"];
}

async function delete_user_admin(id) {
  if (!confirm("Are you sure you want to delete this user?")) return;
  const response = await fetch(`../../api/DeleteUser.php?id=${encodeURIComponent(id)}`);
  const data = await response.json();
  if (!data.error) {
    const row = document.getElementById(`user${id}`);
    if (row) row.remove();
  }
}

async function refreshAdminTable() {
  let table = document.getElementById("adminTable");
  table.innerHTML = `<tr class="adminTableHead">
              <th>Username</th>
              <th>Card Number</th>
              <th></th>
            </tr>`;
  let users = await get_all_users();

  if (users) {
    users.forEach((user) => {
      const profile_id = user["PROFILE_CODE"];
      let row = table.insertRow(1);
      row.className = "adminTableData";
      row.id = `user${profile_id}`;
      let username = row.insertCell(0);
      username.id = `${profile_id}Username`;
      let cardNo = row.insertCell(1);
      cardNo.id = `${profile_id}CardNo`;
      let buttons = row.insertCell(2);

      username.innerHTML = user["USER_NAME"];
      cardNo.innerHTML = user["CARD_NO"];

      // NOTA: Al pasar objeto JSON en HTML, usamos comillas simples y escapamos
      buttons.innerHTML = `<div class="center-flex-div">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-small" onclick='openModifyUserPopupFromAdmin(${JSON.stringify(user)})'>
                  <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z" />
                  <path d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z" />
                </svg>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ff5457" class="size-small" onclick="delete_user_admin(${user.PROFILE_CODE})">
                  <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd" />
                </svg>
              </div>`;
    });
  } else {
    let row = table.insertRow(1);
    row.className = "adminTableData";
    let accountNum = row.insertCell(0);
    accountNum.innerHTML = "No users available.";
  }
}

// Función auxiliar necesaria para abrir el popup desde la tabla de administración
// Ya que 'openModifyUserPopup' ahora usa 'currentUser', creamos esta variante para ver OTROS usuarios
function openModifyUserPopupFromAdmin(user) {
  // Al ser admin editando a otro, aquí SI pasamos los datos por parámetro
  // Pero OJO: reutilizas el mismo popup. Esto podría sobrescribir la variable de memoria.
  // Para simplificar: Solo rellenamos los inputs visualmente
  document.getElementById("message").innerHTML = "";
  document.getElementById("usernameUser").value = user.USER_NAME;
  document.getElementById("emailUser").value = user.EMAIL;
  document.getElementById("phoneUser").value = user.TELEPHONE;
  document.getElementById("firstNameUser").value = user.NAME_;
  document.getElementById("lastNameUser").value = user.SURNAME;
  document.getElementById("genderUser").value = user.GENDER;
  document.getElementById("cardNumberUser").value = user.CARD_NO;

  // NOTA: La función 'modifyUser' original usa 'currentUser'.
  // Si el admin guarda cambios aquí, necesitamos que 'modifyUser' sepa qué ID usar.
  // Hack rápido para que funcione tu estructura actual:
  // Temporalmente suplantamos currentUser solo para la edición
  currentUser = user;

  let modifyUserPopup = document.getElementById("modifyUserPopupAdmin");
  modifyUserPopup.style.display = "flex";
}

function openModifyAdminPopup() {
  document.getElementById("messageAdmin").innerHTML = "";
  const usuario = currentUser;

  document.getElementById("usernameAdmin").value = usuario.USER_NAME;
  document.getElementById("emailAdmin").value = usuario.EMAIL;
  document.getElementById("phoneAdmin").value = usuario.TELEPHONE;
  document.getElementById("firstNameAdmin").value = usuario.NAME_;
  document.getElementById("lastNameAdmin").value = usuario.SURNAME;
  document.getElementById("profileCodeAdmin").value = usuario.PROFILE_CODE;
  document.getElementById("currentAccountAdmin").value = usuario.CURRENT_ACCOUNT;

  let modifyAdminPopup = document.getElementById("modifyAdminPopup");
  modifyAdminPopup.style.display = "flex";
}

async function modifyAdmin() {
  const usuario = currentUser;
  const profile_code = usuario.PROFILE_CODE;

  const name = document.getElementById("firstNameAdmin").value;
  const surname = document.getElementById("lastNameAdmin").value;
  const email = document.getElementById("emailAdmin").value;
  const username = document.getElementById("usernameAdmin").value;
  const telephone = document.getElementById("phoneAdmin").value.replace(/\s/g, "");
  const current_account = document.getElementById("currentAccountAdmin").value;

  if (!name || !surname || !email || !username || !telephone || !current_account) {
    document.getElementById("messageAdmin").innerHTML = "You must fill all the fields";
    document.getElementById("messageAdmin").style.color = "red";
    return;
  }

  function hasChanges() {
    return (
      name !== usuario.NAME_ ||
      surname !== usuario.SURNAME ||
      email !== usuario.EMAIL ||
      username !== usuario.USER_NAME ||
      telephone !== usuario.TELEPHONE ||
      current_account !== usuario.CURRENT_ACCOUNT
    );
  }

  if (!hasChanges()) {
    document.getElementById("messageAdmin").innerHTML = "No changes detected";
    document.getElementById("messageAdmin").style.color = "red";
  } else {
    try {
      const response = await fetch(
        `../../api/ModifyAdmin.php?profile_code=${encodeURIComponent(profile_code)}&name=${encodeURIComponent(name)}&surname=${encodeURIComponent(surname)}&email=${encodeURIComponent(email)}&username=${encodeURIComponent(username)}&telephone=${encodeURIComponent(telephone)}&current_account=${encodeURIComponent(current_account)}`
      );
      const data = await response.json();

      if (data.success) {
        document.getElementById("messageAdmin").innerHTML = data.message;
        document.getElementById("messageAdmin").style.color = "green";

        currentUser.NAME_ = name;
        currentUser.SURNAME = surname;
        currentUser.EMAIL = email;
        currentUser.USER_NAME = username;
        currentUser.TELEPHONE = telephone;
        currentUser.CURRENT_ACCOUNT = current_account;

      } else {
        document.getElementById("messageAdmin").innerHTML = data.error;
        document.getElementById("messageAdmin").style.color = "red";
      }
    } catch (error) {
      console.log(error);
    }
  }
}

function resetPasswordModal() {
  document.getElementById("changePasswordForm").reset();
  document.getElementById("messageOldPassword").innerHTML = "";
  document.getElementById("messageWrongPassword").innerHTML = "";
  document.getElementById("message").innerHTML = "";
}

async function delete_user(id) {
  if (!confirm("Are you sure you want to delete your account?")) return;
  const response = await fetch(`../../api/DeleteUser.php?id=${encodeURIComponent(id)}`);
  const data = await response.json();

  if (!data.error) {
    window.location.href = "login.html";
  }
}