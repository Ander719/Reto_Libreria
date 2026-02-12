import { checkSession } from "./session.js";
import { loadHeader, loadFooter } from "./header.js"

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

        if (data.success) {
            dialogMessage.textContent = "Login exitoso. Redirigiendo...";
            dialog.showModal();

            setTimeout(() => {
                //volver a a la pagina anterior desde la que vino el usuario
                const previousPage = document.referrer || 'main.html';
                window.location.href = previousPage;
            }, 500);
        } else {
            // Mostramos el error en el diálogo
            dialogMessage.textContent = data.error || "Error desconocido durante el login.";
            dialog.showModal();
        }
    });
}

async function login(username, password) {
    try {
        const response = await fetch(`../../api/Login.php`, {
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
            console.error("Error al parsear JSON:", rawText);
            return { success: false, error: "Respuesta del servidor no es JSON válido." };
        }
        if (!response.ok) {
            return { 
                success: false, 
                error: data.error || `Error del servidor (${response.status})` 
            };
        }
        return data; // Si todo fue bien (200), devolvemos los datos tal cual

    } catch (error) {
        console.error("Error en fetch:", error);
        return { success: false, error: "Fallo de red o servidor caído." };
    }
}
