import { loadHeader, loadFooter } from './header.js';
import { checkSession } from './session.js';
import { apiFetch } from './apiClient.js';

init();

async function init() {
  const isLogged = await checkSession();

  await loadHeader("logInSignUp")
  await loadFooter()

  if (isLogged) {
    window.location.replace("main.html");
    return;
  }
}

document.getElementById("signupForm").addEventListener("submit", async function (e) {
  e.preventDefault();

  const username = document.getElementById("username").value;
  const pswd1 = document.getElementById("pswd1").value;
  const pswd2 = document.getElementById("pswd2").value;
  const parrafo = document.getElementById("mensaje");

  if (pswd1 !== pswd2) {
    parrafo.innerText = "Las contraseñas no coinciden.";
    parrafo.style.color = "red";
    return;
  }

  try {
    const data = await apiFetch("../../api/AddUser.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json; charset=utf-8",
      },
      body: JSON.stringify({ username, pswd1 }),
      credentials: "include",
    });
    console.log("Respuesta AddUser:", data);

    if (data.status === "success") {
      parrafo.innerText = data.message || "Usuario creado con éxito.";
      parrafo.style.color = "green";

      setTimeout(() => {
        window.location.href = "login.html";
      }, 1500);
    } else {
      parrafo.innerText = data.message || "Error al crear usuario";
      parrafo.style.color = "red";
    }
  } catch (error) {
    console.error(error);
    parrafo.innerText = error.message || "Error de conexión con el servidor.";
    parrafo.style.color = "red";
  }
});
