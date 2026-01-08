document.addEventListener("DOMContentLoaded", async () => {
  /******************************************************************************************************
   *****************************************VARIABLE DECLARATION*****************************************
   ******************************************************************************************************/

  // Cargar el perfil actual del localStorage
  let profile = JSON.parse(localStorage.getItem("actualProfile"));

  /* ---------- MODIFICACIÓN: DETECTAR MODO DESDE URL ---------- */
  // Leemos si la URL tiene ?mode=deleteUser o ?mode=modifyUser
  const urlParams = new URLSearchParams(window.location.search);
  const mode = urlParams.get('mode'); 

  // Referencias a elementos que usaremos para la lógica automática
  const adminTableModal = document.getElementById("adminTableModal");
  const deleteBtn = document.getElementById("deleteBtn"); // Botón de borrar dentro del popup de edición

  // Si detectamos un modo en la URL y el usuario es Administrador (tiene CURRENT_ACCOUNT)
  if (mode && profile && ["CURRENT_ACCOUNT"] in profile) {
      
      // Abrimos el modal de la tabla automáticamente
      adminTableModal.style.display = "block";
      
      // Llamamos a la función de refrescar tabla pasándole el modo
      refreshAdminTable(mode);

      // Opcional: Si entramos en modo "Delete User" desde la tabla, 
      // ocultamos el botón de borrar DENTRO del popup de detalles para que no sea confuso
      if (deleteBtn && mode === 'deleteUser') {
          deleteBtn.style.display = "none";
      }
  }
  /* ----------------------------------------------------------- */

  /* ----------HOME---------- */
  const homeBtn = document.getElementById("adjustData");

  /* ----------USER POPUP---------- */
  const modifyUserPopup = document.getElementById("modifyUserPopupAdmin");
  const changePwdBtn = document.getElementById("changePwdBtn");
  const saveBtnUser = document.getElementById("saveBtnUser");

  /* ----------ADMIN POPUP---------- */
  const modifyAdminPopup = document.getElementById("modifyAdminPopup");
  const closeAdminSpan = document.getElementsByClassName("close")[0];
  const changePwdBtnAdmin = document.getElementById("changePwdBtnAdmin");
  // const adminTableModal ya definido arriba
  const modifyAdminBtn = document.getElementById("modifySelfButton");
  const saveBtnAdmin = document.getElementById("saveBtnAdmin");

  /* ----------SHARED ELEMENTS---------- */
  const changePwdModal = document.getElementById("changePasswordModal");
  // const deleteBtn ya definido arriba
  const closePasswordSpan =
    document.getElementsByClassName("closePasswordSpan")[0];

  /******************************************************************************************************
   ****************************************BUTTON FUNCTIONALITIES****************************************
   ******************************************************************************************************/

  /* ----------HOME---------- */
  // Abre un popup dependiendo de si es user o admin
  homeBtn.onclick = function () {
    // Recargamos el perfil por si acaso
    profile = JSON.parse(localStorage.getItem("actualProfile"));
    
    if (profile && ["CARD_NO"] in profile) {
      document.getElementById("message").innerHTML = "";
      openModifyUserPopup(profile);
    } else if (profile && ["CURRENT_ACCOUNT"] in profile) {
      // Si entra como admin desde el botón "Adjust Data", muestra la tabla normal (ambos iconos)
      refreshAdminTable(); 
      adminTableModal.style.display = "block";
      // Ocultar botón borrar en popup usuario (lógica original)
      if(deleteBtn) deleteBtn.style.display = "none";
    }
  };

  /* ----------USER POPUP---------- */
  changePwdBtn.onclick = function () {
    changePwdModal.style.display = "block";
    resetPasswordModal();
  };

  saveBtnUser.onclick = function () {
    modifyUser();
  };

  /* ----------ADMIN POPUP---------- */
  closeAdminSpan.onclick = function () {
    adminTableModal.style.display = "none";
    // Limpiamos la URL al cerrar para que si recarga no se vuelva a abrir solo
    window.history.pushState({}, document.title, window.location.pathname);
  };

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
  if(deleteBtn) {
      deleteBtn.onclick = function () {
        delete_user(profile["PROFILE_CODE"]);
      };
  }

  closePasswordSpan.onclick = function () {
    changePwdModal.style.display = "none";
  };

  // Cerrar popups al hacer click fuera
  window.onclick = function (event) {
    if (event.target == adminTableModal) {
      adminTableModal.style.display = "none";
      // Limpiamos URL
      window.history.pushState({}, document.title, window.location.pathname);
    } else if (event.target == modifyUserPopup) {
      modifyUserPopup.style.display = "none";
    } else if (event.target == modifyAdminPopup) {
      modifyAdminPopup.style.display = "none";
    } else if (event.target == changePwdModal) {
      changePwdModal.style.display = "none";
    }
  };

  // Lógica de cambio de contraseña
  document
    .getElementById("changePasswordForm")
    .addEventListener("submit", async function (e) {
      e.preventDefault();

      document.getElementById("messageOldPassword").innerHTML = "";
      document.getElementById("messageWrongPassword").innerHTML = "";
      document.getElementById("message").innerHTML = "";

      let actualProfile;
      // Recargar perfil para asegurar datos frescos
      profile = JSON.parse(localStorage.getItem("actualProfile"));

      if (["CARD_NO"] in profile) {
        actualProfile = JSON.parse(localStorage.getItem("actualUser")) || profile;
      } else if (["CURRENT_ACCOUNT"] in profile) {
        actualProfile = profile;
      }

      const profile_code = actualProfile["PROFILE_CODE"];
      const userPassword = actualProfile["PSWD"];
      const password = document.getElementById("currentPassword").value;
      const newPassword = document.getElementById("newPassword").value;
      const confirmPassword =
        document.getElementById("confirmNewPassword").value;

      let hasErrors = false;

      if (userPassword != password) {
        document.getElementById("messageOldPassword").innerHTML =
          "That is not your current password";
        hasErrors = true;
      }

      if (userPassword == newPassword) {
        document.getElementById("messageWrongPassword").innerHTML =
          "Password used before, try another one";
        hasErrors = true;
      }

      if (newPassword != confirmPassword) {
        document.getElementById("messageWrongPassword").innerHTML =
          "The passwords are not the same";
        hasErrors = true;
      }

      if (!hasErrors) {
        try {
          const response = await fetch("../../api/ModifyPassword.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify({
              profile_code: profile_code,
              password: newPassword,
            }),
          });

          const data = await response.json();

          if (data.success) {
            actualProfile.PSWD = newPassword;
            document.getElementById("messageSuccessPassword").innerHTML =
              "Password correctly changed";
            
            // Actualizar localStorage
            if (["CARD_NO"] in profile) {
              localStorage.setItem("actualUser", JSON.stringify(actualProfile));
              // También actualizar el perfil principal si es el mismo usuario
              if(profile.PROFILE_CODE === actualProfile.PROFILE_CODE) {
                  localStorage.setItem("actualProfile", JSON.stringify(actualProfile));
              }
            } else if (["CURRENT_ACCOUNT"] in profile) {
              localStorage.setItem(
                "actualProfile",
                JSON.stringify(actualProfile)
              );
            }

            setTimeout(() => {
              document.getElementById("messageSuccessPassword").innerHTML = ""; 
              document.getElementById("changePasswordForm").reset(); 
            }, 3000);
          } else {
            document.getElementById("messageSuccessPassword").innerHTML =
              data.error;
            document.getElementById("messageSuccessPassword").style.color =
              "red";
          }
        } catch (error) {
          console.log(error);
        }
      }
    });
});

/******************************************************************************************************
 ***********************************************METHODS************************************************
 ******************************************************************************************************/

/* ----------HOME---------- */
function openModifyUserPopup(actualProfile) {
  document.getElementById("message").innerHTML = "";
  localStorage.setItem("actualUser", JSON.stringify(actualProfile));

  const usuario = {
    profile_code: actualProfile.PROFILE_CODE,
    password: actualProfile.PSWD,
    email: actualProfile.EMAIL,
    username: actualProfile.USER_NAME,
    telephone: actualProfile.TELEPHONE,
    name: actualProfile.NAME_,
    surname: actualProfile.SURNAME,
    gender: actualProfile.GENDER,
    card_no: actualProfile.CARD_NO,
  };

  document.getElementById("usernameUser").value = usuario.username;
  
  if (usuario.email) {
    document.getElementById("emailUser").value = usuario.email;
    document.getElementById("phoneUser").value = usuario.telephone;
    document.getElementById("firstNameUser").value = usuario.name;
    document.getElementById("lastNameUser").value = usuario.surname;
    document.getElementById("genderUser").value = usuario.gender;
    document.getElementById("cardNumberUser").value = usuario.card_no;
  }

  let modifyUserPopup = document.getElementById("modifyUserPopupAdmin");
  modifyUserPopup.style.display = "flex";
}

/* ----------USER POPUP---------- */
async function modifyUser() {
  const actualProfile = JSON.parse(localStorage.getItem("actualUser"));

  const usuario = {
    profile_code: actualProfile.PROFILE_CODE,
    // ... resto de campos
    name: actualProfile.NAME_,
    surname: actualProfile.SURNAME,
    email: actualProfile.EMAIL,
    username: actualProfile.USER_NAME,
    telephone: actualProfile.TELEPHONE,
    gender: actualProfile.GENDER,
    card_no: actualProfile.CARD_NO
  };

  const profile_code = usuario.profile_code;
  const name = document.getElementById("firstNameUser").value;
  const surname = document.getElementById("lastNameUser").value;
  const email = document.getElementById("emailUser").value;
  const username = document.getElementById("usernameUser").value;
  const telephone = document
    .getElementById("phoneUser")
    .value.replace(/\s/g, ""); 
  const gender = document.getElementById("genderUser").value;
  const card_no = document.getElementById("cardNumberUser").value;

  if (
    !name ||
    !surname ||
    !email ||
    !username ||
    !telephone ||
    !gender ||
    !card_no
  ) {
    document.getElementById("message").innerHTML =
      "You must fill all the fields";
    document.getElementById("message").style.color = "red";
    return;
  }

  function hasChanges() {
    let changes = false;
    if (
      name !== usuario.name ||
      surname !== usuario.surname ||
      email !== usuario.email ||
      username !== usuario.username ||
      telephone !== usuario.telephone ||
      gender !== usuario.gender ||
      card_no !== usuario.card_no
    ) {
      changes = true;
    }
    return changes;
  }

  if (!hasChanges()) {
    document.getElementById("message").innerHTML = "No changes detected";
    document.getElementById("message").style.color = "red";
  } else {
    try {
      const response = await fetch(
        `../../api/ModifyUser.php?profile_code=${encodeURIComponent(
          profile_code
        )}&name=${encodeURIComponent(name)}&surname=${encodeURIComponent(
          surname
        )}&email=${encodeURIComponent(email)}&username=${encodeURIComponent(
          username
        )}&telephone=${encodeURIComponent(
          telephone
        )}&gender=${encodeURIComponent(gender)}&card_no=${encodeURIComponent(
          card_no
        )}`
      );
      const data = await response.json();

      if (data.success) {
        document.getElementById("message").innerHTML = data.message;
        document.getElementById("message").style.color = "green";

        actualProfile.NAME_ = name;
        actualProfile.SURNAME = surname;
        actualProfile.EMAIL = email;
        actualProfile.USER_NAME = username;
        actualProfile.TELEPHONE = telephone;
        actualProfile.CARD_NO = card_no;
        actualProfile.GENDER = gender;

        localStorage.setItem("actualUser", JSON.stringify(actualProfile));

        // Refrescar tabla si el admin está viéndola
        if (
          ["CURRENT_ACCOUNT"] in
          JSON.parse(localStorage.getItem("actualProfile"))
        ) {
          // Detectamos el modo actual de la URL para refrescar manteniendo el estado visual
          const urlParams = new URLSearchParams(window.location.search);
          const currentMode = urlParams.get('mode');
          refreshAdminTable(currentMode);
        } else {
          localStorage.setItem("actualProfile", JSON.stringify(actualProfile));
        }
      } else {
        document.getElementById("message").innerHTML = data.error;
        document.getElementById("message").style.color = "red";
      }
    } catch (error) {
      console.log(error);
    }
  }
}

/* ----------ADMIN POPUP---------- */
async function get_all_users() {
  const response = await fetch("../../api/GetAllUsers.php");
  const data = await response.json();
  return data["resultado"];
}

async function delete_user_admin(id) {
  if (!confirm("Are you sure you want to delete this user?")) return;

  const response = await fetch(
    `../../api/DeleteUser.php?id=${encodeURIComponent(id)}`
  );

  const data = await response.json();

  if (data.error) {
    console.log("Error deleting user: ", data.error);
  } else {
    // Eliminar la fila visualmente
    let row = document.getElementById(`user${id}`);
    if (row) row.remove();
  }
}

/* ---------- MODIFICACIÓN: FUNCIÓN DE REFRESCO CON MODO ---------- */
async function refreshAdminTable(mode = null) {
  let table = document.getElementById("adminTable");
  
  // Header Action
  table.innerHTML = `<tr class="adminTableHead">
              <th>Username</th>
              <th>Card Number</th>
              <th>Action</th>
            </tr>`;
            
  let users = await get_all_users();

  // Estilos para ocultar botones según el modo
  const styleHideModify = (mode === 'deleteUser') ? 'display:none;' : '';
  const styleHideDelete = (mode === 'modifyUser') ? 'display:none;' : '';

  if (users) {
    users.forEach((user) => {
      const profile_id = user["PROFILE_CODE"];
      
      // Insertar en la tabla correcta (table, no adminTable global)
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
      
      // Aplicamos la visibilidad según el modo
      buttons.innerHTML = `<div class="center-flex-div">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="currentColor"
                  class="size-small"
                  style="${styleHideModify} cursor: pointer;"
                  onclick='openModifyUserPopup(${JSON.stringify(user)})'
                >
                  <path
                    d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z"
                  />
                  <path
                    d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z"
                  />
                </svg>

                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="#ff5457"
                  class="size-small"
                  style="${styleHideDelete} cursor: pointer;"
                  onclick="delete_user_admin(${user.PROFILE_CODE})" 
                >
                  <path
                    fill-rule="evenodd"
                    d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z"
                    clip-rule="evenodd"
                  />
                </svg>
              </div>`;
    });
  } else {
    let row = table.insertRow(1);
    row.className = "adminTableData";
    let username = row.insertCell(0);
    let accountNum = row.insertCell(1);
    let buttons = row.insertCell(2);
    accountNum.innerHTML = "No users available.";
  }
}

function openModifyAdminPopup() {
  document.getElementById("messageAdmin").innerHTML = "";
  const actualProfile = JSON.parse(localStorage.getItem("actualProfile"));
  let modifyAdminPopup = document.getElementById("modifyAdminPopup");

  const usuario = {
    profile_code: actualProfile.PROFILE_CODE,
    password: actualProfile.PSWD,
    email: actualProfile.EMAIL,
    username: actualProfile.USER_NAME,
    telephone: actualProfile.TELEPHONE,
    name: actualProfile.NAME_,
    surname: actualProfile.SURNAME,
    current_account: actualProfile.CURRENT_ACCOUNT,
  };

  document.getElementById("usernameAdmin").value = usuario.username;
  document.getElementById("emailAdmin").value = usuario.email;
  document.getElementById("phoneAdmin").value = usuario.telephone;
  document.getElementById("firstNameAdmin").value = usuario.name;
  document.getElementById("lastNameAdmin").value = usuario.surname;
  document.getElementById("profileCodeAdmin").value = usuario.profile_code;
  document.getElementById("currentAccountAdmin").value =
    usuario.current_account;

  modifyAdminPopup.style.display = "flex";
}

async function modifyAdmin() {
  const actualProfile = JSON.parse(localStorage.getItem("actualProfile"));
  // (La lógica es idéntica a modifyUser pero con datos de admin, la mantengo igual)
  const usuario = {
    profile_code: actualProfile.PROFILE_CODE,
    password: actualProfile.PSWD,
    email: actualProfile.EMAIL,
    username: actualProfile.USER_NAME,
    telephone: actualProfile.TELEPHONE,
    name: actualProfile.NAME_,
    surname: actualProfile.SURNAME,
    current_account: actualProfile.CURRENT_ACCOUNT,
  };

  const profile_code = usuario.profile_code;
  const name = document.getElementById("firstNameAdmin").value;
  const surname = document.getElementById("lastNameAdmin").value;
  const email = document.getElementById("emailAdmin").value;
  const username = document.getElementById("usernameAdmin").value;
  const telephone = document
    .getElementById("phoneAdmin")
    .value.replace(/\s/g, "");
  const current_account = document.getElementById("currentAccountAdmin").value;

  if (
    !name ||
    !surname ||
    !email ||
    !username ||
    !telephone ||
    !current_account
  ) {
    document.getElementById("messageAdmin").innerHTML =
      "You must fill all the fields";
    document.getElementById("messageAdmin").style.color = "red";
    return;
  }

  function hasChanges() {
    let changes = false;
    if (
      name !== usuario.name ||
      surname !== usuario.surname ||
      email !== usuario.email ||
      username !== usuario.username ||
      telephone !== usuario.telephone ||
      current_account !== usuario.current_account
    ) {
      changes = true;
    }
    return changes;
  }

  if (!hasChanges()) {
    document.getElementById("messageAdmin").innerHTML = "No changes detected";
    document.getElementById("messageAdmin").style.color = "red";
  } else {
    try {
      const response = await fetch(
        `../../api/ModifyAdmin.php?profile_code=${encodeURIComponent(
          profile_code
        )}&name=${encodeURIComponent(name)}&surname=${encodeURIComponent(
          surname
        )}&email=${encodeURIComponent(email)}&username=${encodeURIComponent(
          username
        )}&telephone=${encodeURIComponent(
          telephone
        )}&current_account=${encodeURIComponent(current_account)}`
      );

      const data = await response.json();

      if (data.success) {
        document.getElementById("messageAdmin").innerHTML = data.message;
        document.getElementById("messageAdmin").style.color = "green";

        actualProfile.NAME_ = name;
        actualProfile.SURNAME = surname;
        actualProfile.EMAIL = email;
        actualProfile.USER_NAME = username;
        actualProfile.TELEPHONE = telephone;
        actualProfile.CURRENT_ACCOUNT = current_account;

        localStorage.setItem("actualProfile", JSON.stringify(actualProfile));
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
  if (!confirm("Are you sure you want to your account?")) return;

  const response = await fetch(
    `../../api/DeleteUser.php?id=${encodeURIComponent(id)}`
  );

  const data = await response.json();

  if (data.error) {
     console.log("Error deleting user: ", data.error);
  } else {
    window.location.href = "login.html";
  }
}