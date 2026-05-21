import { checkSession } from "./session.js";
import { loadHeader, loadFooter } from "./header.js"
import { apiFetch } from "./apiClient.js";

init();

/**
 * Carga la pagina de login y redirige si ya hay sesion.
 *
 * @returns {Promise<void>}
 */
async function init() {
    const isLogged = await checkSession();

    await loadHeader("logInSignUp");
    await loadFooter();

    if (isLogged) {
        window.location.replace("main.html");
        return;
    }
}
const dialog = document.getElementById("statusDialog");
const dialogMessage = document.getElementById("dialogMessage");
const loginForm = document.getElementById("loginForm");

if (loginForm) {
    // Al entrar bien, vuelve a la pagina desde la que venia el usuario.
    loginForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        const username = document.getElementById("username").value;
        const password = document.getElementById("password").value;

        let data = await login(username, password);

        if (data.status === "success") {
            dialogMessage.textContent = "Login exitoso. Redirigiendo...";
            dialog.showModal();

            setTimeout(() => {
                const previousPage = document.referrer || 'main.html';
                window.location.href = previousPage;
            }, 500);
        } else {
            dialogMessage.textContent = data.message || "Error desconocido durante el login.";
            dialog.showModal();
        }
    });
}

/**
 * Envia usuario y contrasena al login manteniendo cookies de sesion.
 *
 * @param {string} username Nombre de usuario.
 * @param {string} password Contrasena en claro enviada por HTTPS/local al servidor.
 * @returns {Promise<{status: string, message?: string, data?: any}>} Resultado del login.
 */
async function login(username, password) {
    try {
        const data = await apiFetch(`../../api/Login.php`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ username, password }),
            credentials: 'include',
        });
        console.log("Respuesta Login:", data);
        return data;

    } catch (error) {
        console.error("Error en fetch:", error);
        return { status: "error", message: error.message || "Fallo de red o servidor caído." };
    }
}
