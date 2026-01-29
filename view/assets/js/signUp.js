import { loadHeader } from './header.js';
import { checkSession } from './session.js';

init();

async function init() {
  const isLogged = await checkSession();

  await loadHeader("logInSignUp")

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
    const response = await fetch("../../api/AddUser.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json; charset=utf-8",
      },
      body: JSON.stringify({ username, pswd1 }),
      credentials: "include",
    });

    const rawText = await response.text();

    let data;
    try {
      data = JSON.parse(response.ok ? rawText : "{}");
    } catch (jsonError) {
      throw new Error("Respuesta inválida del servidor: " + jsonError.message);
    }

    if (data.success) {
      parrafo.innerText = "Usuario creado con éxito.";
      parrafo.style.color = "green";

      setTimeout(() => {
        window.location.href = "main.html";
      }, 1000);
    } else {
      // Mostramos el error que viene del controlador
      parrafo.innerText = data.error || "Error al crear usuario";
      parrafo.style.color = "red";
    }
  } catch (error) {
    console.error(error);
    parrafo.innerText = "Error de conexión o servidor.";
    parrafo.style.color = "red";
  }
});
