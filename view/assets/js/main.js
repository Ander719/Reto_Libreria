<<<<<<< HEAD
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
  // Verificamos existencia para evitar errores si el elemento no está en el DOM
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
=======
document.addEventListener('DOMContentLoaded', () => {
    // --- REFERENCIAS ---
    const adjustDataBtn = document.getElementById('adjustData');
    const adminPanelSection = document.getElementById('adminPanelSection');
    const tableBody = document.getElementById('adminTableBody');
>>>>>>> 3f91231 (ultimos cambios de la ventana main.html, no funciona  el registro ni las ventanas de crud libro)
    
    // Modales
    const modifyUserPopup = document.getElementById('modifyUserPopupAdmin');
    const modifyAdminPopup = document.getElementById('modifyAdminPopup');
    
    // Botones Guardar
    const saveBtnUser = document.getElementById('saveBtnUser');
    const saveBtnAdmin = document.getElementById('saveBtnAdmin');
    
    // BOTÓN BORRAR (DEL MODAL)
    const deleteBtnModal = document.getElementById('deleteBtn'); 

    // Botones Cerrar
    const closeUser = document.getElementById('closeUserModal');
    const closeAdmin = document.getElementById('closeAdminModal');

    window.allUsers = [];

    // 1. INICIO
    checkUserRole();

    // 2. EVENTOS DE APERTURA
    if (adjustDataBtn) {
        adjustDataBtn.addEventListener('click', (e) => {
            e.preventDefault();
            loadMyProfile();
        });
    }
<<<<<<< HEAD
  };
  /* ---------- RESTO DE FUNCIONALIDADES (Solo funcionan si el modal se abre) ---------- */
=======
>>>>>>> 3f91231 (ultimos cambios de la ventana main.html, no funciona  el registro ni las ventanas de crud libro)

    const modifySelfBtn = document.getElementById('modifySelfButton');
    if (modifySelfBtn) {
        modifySelfBtn.addEventListener('click', (e) => {
            e.preventDefault();
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

<<<<<<< HEAD
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
=======
    // 4. EVENTO DE BORRADO DESDE EL MODAL (NUEVO)
    if (deleteBtnModal) {
        deleteBtnModal.addEventListener('click', (e) => {
            e.preventDefault();
>>>>>>> 3f91231 (ultimos cambios de la ventana main.html, no funciona  el registro ni las ventanas de crud libro)
            
            // Recuperamos el ID que guardamos al abrir el modal
            const targetId = deleteBtnModal.getAttribute('data-target-id');
            
            if(!targetId) {
                alert("Error: No se ha identificado el usuario a borrar.");
                return;
            }

<<<<<<< HEAD
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
      } catch (error) {
        console.log(error);
      }
    }
  });
}
/* ---------- METHODS ---------- */

function openModifyUserPopup() {
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
=======
            if(confirm("¿ESTÁS SEGURO? Esta acción borrará la cuenta permanentemente.")) {
                // Usamos la misma API que la tabla
                fetch(`../../api/DeleteUser.php?id=${targetId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.result === true) {
                            alert("Cuenta eliminada correctamente.");
                            
                            // Si me borré a mí mismo -> Ir al Login
                            if (data.isSelfDelete === true) {
                                window.location.href = 'login.html';
                            } else {
                                // Si borré a otro (admin borrando usuario) -> Cerrar modal y recargar tabla
                                document.getElementById('modifyUserPopupAdmin').style.display = 'none';
                                loadUsers();
                            }
                        } else {
                            alert("Error al borrar: " + (data.error || "Desconocido"));
                        }
                    })
                    .catch(err => console.error("Error:", err));
            }
        });
>>>>>>> 3f91231 (ultimos cambios de la ventana main.html, no funciona  el registro ni las ventanas de crud libro)
    }

<<<<<<< HEAD
  if (!hasChanges()) {
    document.getElementById("message").innerHTML = "No changes detected";
    document.getElementById("message").style.color = "red";
  } else {
    try {
      const response = await fetch(
        `../../api/ModifyUser.php?profile_code=${encodeURIComponent(profile_code)}&name=${encodeURIComponent(name)}&surname=${encodeURIComponent(surname)}&email=${encodeURIComponent(email)}&username=${encodeURIComponent(username)}&telephone=${encodeURIComponent(telephone)}&gender=${encodeURIComponent(gender)}&card_no=${encodeURIComponent(card_no)}`
      );
      const data = await response.json();
=======
    // Cerrar Modales
    if(closeUser) closeUser.onclick = () => modifyUserPopup.style.display = 'none';
    if(closeAdmin) closeAdmin.onclick = () => modifyAdminPopup.style.display = 'none';
    window.onclick = (e) => {
        if(e.target === modifyUserPopup) modifyUserPopup.style.display = 'none';
        if(e.target === modifyAdminPopup) modifyAdminPopup.style.display = 'none';
    };
>>>>>>> 3f91231 (ultimos cambios de la ventana main.html, no funciona  el registro ni las ventanas de crud libro)

    // --- FUNCIONES ---

    function checkUserRole() {
        if(adminPanelSection) adminPanelSection.style.display = 'none';
        fetch('../../api/CheckUserType.php')
            .then(res => res.json())
            .then(data => {
                if (data.isAdmin === true && adminPanelSection) {
                    adminPanelSection.style.display = 'flex';
                    loadUsers();
                }
            });
    }

    function loadUsers() {
        if (!tableBody) return;
        const urlParams = new URLSearchParams(window.location.search);
        const mode = urlParams.get('mode'); 

        fetch('../../api/GetAllUsers.php')
            .then(res => res.json())
            .then(data => {
                tableBody.innerHTML = '';
                const users = data.resultado || [];
                window.allUsers = users;

                if(users.length > 0){
                    users.forEach((user, index) => {
                        const tr = document.createElement('tr');
                        const userId = user.PROFILE_CODE || user.id;
                        let buttonsHTML = '';
                        
                        // Lógica de visualización de botones en tabla
                        if (mode === 'modifyUser') {
                            buttonsHTML = `<button class="saveBtn" style="padding:5px 10px; font-size:0.8rem;" onclick="prepareEditUser(${index})">Edit</button>`;
                        } else if (mode === 'deleteUser') {
                            buttonsHTML = `<button class="deleteBtn" style="padding:5px 10px; font-size:0.8rem; color:red; border-color:red;" onclick="deleteUser('${userId}')">Del</button>`;
                        } else {
                            buttonsHTML = `
                                <button class="saveBtn" style="padding:5px 10px; font-size:0.8rem; margin-right:5px;" onclick="prepareEditUser(${index})">Edit</button>
                                <button class="deleteBtn" style="padding:5px 10px; font-size:0.8rem; color:red; border-color:red;" onclick="deleteUser('${userId}')">Del</button>
                            `;
                        }

                        tr.innerHTML = `
                            <td>${user.USER_NAME || user.username}</td>
                            <td>${user.NAME_ || user.name} ${user.SURNAME || user.surname}</td>
                            <td>${user.EMAIL || user.email}</td>
                            <td>${buttonsHTML}</td>
                        `;
                        tableBody.appendChild(tr);
                    });
                }
            });
    }

    window.prepareEditUser = function(index) {
        const u = window.allUsers[index];
        if (!u) return;
        
        setValue('firstNameUser', u.NAME_ || u.name);
        setValue('lastNameUser', u.SURNAME || u.surname);
        setValue('emailUser', u.EMAIL || u.email);
        setValue('usernameUser', u.USER_NAME || u.username);
        setValue('phoneUser', u.TELEPHONE || u.telephone);
        setValue('cardNumberUser', u.CARD_NO || '');
        if(document.getElementById('genderUser')) document.getElementById('genderUser').value = u.GENDER || 'Other';

        // GUARDAMOS EL ID EN EL BOTÓN "SAVE" Y EN EL BOTÓN "DELETE"
        const targetId = u.PROFILE_CODE;
        if(saveBtnUser) saveBtnUser.setAttribute('data-target-id', targetId);
        
        // --- AQUÍ ESTÁ LA CLAVE DEL BORRADO ---
        if(deleteBtnModal) deleteBtnModal.setAttribute('data-target-id', targetId);

        document.getElementById('modifyUserPopupAdmin').style.display = 'flex';
    };

    function loadMyProfile() {
        fetch('../../api/GetProfile.php')
            .then(res => res.json())
            .then(data => {
                if (data.exito && data.user) {
                    const u = data.user;
                    const isAdmin = (u.ROLE_TYPE === 'admin') || (u.CURRENT_ACCOUNT !== undefined);
                    const targetId = u.PROFILE_CODE;

                    if (isAdmin) {
                        setValue('firstNameAdmin', u.NAME_ || u.name);
                        setValue('lastNameAdmin', u.SURNAME || u.surname);
                        setValue('emailAdmin', u.EMAIL || u.email);
                        setValue('usernameAdmin', u.USER_NAME || u.username);
                        setValue('phoneAdmin', u.TELEPHONE || u.telephone);
                        setValue('profileCodeAdmin', u.PROFILE_CODE);
                        setValue('currentAccountAdmin', u.CURRENT_ACCOUNT);
                        
                        // En Admin Modal no solemos poner delete, pero si lo hubiera:
                        // if(deleteBtnAdmin) deleteBtnAdmin.setAttribute('data-target-id', targetId);
                        
                        document.getElementById('modifyAdminPopup').style.display = 'flex';
                    } else {
                        setValue('firstNameUser', u.NAME_ || u.name);
                        setValue('lastNameUser', u.SURNAME || u.surname);
                        setValue('emailUser', u.EMAIL || u.email);
                        setValue('usernameUser', u.USER_NAME || u.username);
                        setValue('phoneUser', u.TELEPHONE || u.telephone);
                        setValue('cardNumberUser', u.CARD_NO);
                        if(document.getElementById('genderUser')) document.getElementById('genderUser').value = u.GENDER || 'Other';
                        
                        // GUARDAR ID PARA BORRARSE A UNO MISMO
                        if(deleteBtnModal) deleteBtnModal.setAttribute('data-target-id', targetId);
                        
                        document.getElementById('modifyUserPopupAdmin').style.display = 'flex';
                    }
                }
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
            let genderVal = document.getElementById('genderUser') ? document.getElementById('genderUser').value : 'Other';
            formData.append('gender', genderVal);
            targetId = document.getElementById('saveBtnUser').getAttribute('data-target-id');
        }

        if (targetId) formData.append('target_id', targetId);
        formData.append('role', role);

        fetch('../../api/modifyUser.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                alert("Datos guardados.");
                loadUsers();
                document.getElementById('modifyUserPopupAdmin').style.display = 'none';
                document.getElementById('modifyAdminPopup').style.display = 'none';
            } else {
                alert("Error: " + (data.error || "Desconocido"));
            }
        });
    }

<<<<<<< HEAD
async function get_all_users() {
  const response = await fetch("../../api/GetAllUsers.php");
  const data = await response.json();
  return data["resultado"];
}

async function delete_user_admin(id) {
  if (!confirm("Are you sure you want to delete this user?")) return;
  const response = await fetch(`../../api/DeleteUser.php?id=${encodeURIComponent(id)}`);
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

  document.getElementById("usernameAdmin").value = usuario.username;
  document.getElementById("emailAdmin").value = usuario.email;
  document.getElementById("phoneAdmin").value = usuario.telephone;
  document.getElementById("firstNameAdmin").value = usuario.name;
  document.getElementById("lastNameAdmin").value = usuario.surname;
  document.getElementById("profileCodeAdmin").value = usuario.profile_code;
  document.getElementById("currentAccountAdmin").value =
    usuario.current_account;

  let modifyAdminPopup = document.getElementById("modifyAdminPopup");
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
=======
    function setValue(id, val) {
        const el = document.getElementById(id);
        if(el) el.value = val || '';
>>>>>>> 3f91231 (ultimos cambios de la ventana main.html, no funciona  el registro ni las ventanas de crud libro)
    }

<<<<<<< HEAD
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
  if (!confirm("Are you sure you want to delete your account?")) return;
  const response = await fetch(`../../api/DeleteUser.php?id=${encodeURIComponent(id)}`);
  const data = await response.json();

  if (data.error) {
     console.log("Error deleting user: ", data.error);
  } else {
    window.location.href = "login.html";
  }
}
=======
    // BORRADO DESDE LA TABLA
    window.deleteUser = function(id) {
        if(confirm("¿Eliminar usuario permanentemente?")) {
            fetch(`../../api/DeleteUser.php?id=${id}`).then(r => r.json()).then(d => {
                if(d.result) {
                    if(d.isSelfDelete) window.location.href='login.html';
                    else loadUsers();
                } else alert("Error al eliminar");
            });
        }
    };
});
>>>>>>> 3f91231 (ultimos cambios de la ventana main.html, no funciona  el registro ni las ventanas de crud libro)
