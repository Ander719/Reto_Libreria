import { checkSession, currentUser } from "./session.js";

init();

async function init() {
    const isLogged = await checkSession();

    if (isLogged) {
        if (currentUser.rol === 'admin') {
            window.location.replace("opcAdmin.html");
        } else {
            window.location.replace("main.html");
        }
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

        console.log("Intentando login con:", username); // Debug

        let data = await login(username, password);
        console.log("Respuesta servidor:", data); // Debug

        if (data.success) {
            // Mostramos el error en el diálogo
            dialogMessage.textContent = "Login exitoso. Redirigiendo...";
            dialog.showModal();

            // Simplemente rediriges. La cookie ya está grabada en el navegador.
            if (data.rol === "admin") {
                window.location.href = "opcAdmin.html";
            } else {
                window.location.href = "main.html";
            }
        } else {
            // Mostramos el error en el diálogo
            dialogMessage.textContent = data.error || "Error desconocido durante el login.";
            dialog.showModal();
        }
    });
}

async function login(username, password) {
    try {
        const response = await fetch(`../../api/LogIn.php`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ username, password }),
            credentials: 'include', // Importante para enviar/recibir cookies
        });
        const rawText = await response.text();

        let data;
        try {
            data = JSON.parse(rawText);
        } catch (e) {
            return { success: false, error: "Error grave/formato: " + rawText };
        }

        if (!response.ok) {
            return {
                success: false, error: data.error || `Error de conexión (${response.status})`
            };
        }

        return data; // Si todo fue bien (200), devolvemos los datos tal cual

    } catch (error) {
        console.error("Error en fetch:", error);
        return { success: false, error: "Fallo de red o servidor caído." };
    }
}
