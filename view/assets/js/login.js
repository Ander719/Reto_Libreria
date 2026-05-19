import { checkSession } from "./session.js";
import { loadHeader, loadFooter } from "./header.js"
import { apiFetch } from "./apiClient.js";

init();

async function init() {
    const isLogged = await checkSession();

    await loadHeader("logInSignUp");
    await loadFooter();

    if (isLogged) {
        window.location.replace("main.html");
        return; // Detenemos la ejecución del script
    }
}
const dialog = document.getElementById("statusDialog");
const dialogMessage = document.getElementById("dialogMessage");
const loginForm = document.getElementById("loginForm");

if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        const username = document.getElementById("username").value;
        const password = document.getElementById("password").value;

        //console.log("Intentando login con:", username);

        let data = await login(username, password);
        //console.log("Respuesta servidor:", data);

        if (data.status === 'success') {
            dialogMessage.textContent = "Login exitoso. Redirigiendo...";
            dialog.showModal();

            setTimeout(() => {
                // Volver a la página anterior desde la que vino el usuario o al inicio
                const previousPage = document.referrer || 'main.html';
                window.location.href = previousPage;
            }, 500);
        } else {
            // Mostramos el mensaje de error estandarizado de la API
            dialogMessage.textContent = data.message || "Error desconocido durante el login.";
            dialog.showModal();
        }
    });
}

async function login(username, password) {
    try {
        const data = await apiFetch(`../../api/Login.php`, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`,
            credentials: 'include'
        });
        console.log("Respuesta Login:", data);
        return data;

    } catch (error) {
        console.error("Error en fetch:", error);
        return { status: "error", message: error.message || "Fallo de conexión con el servidor." };
    }
}
