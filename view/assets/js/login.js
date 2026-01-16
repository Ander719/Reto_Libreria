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

const loginForm = document.getElementById("loginForm");

if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        const username = document.getElementById("username").value;
        const password = document.getElementById("password").value;

        console.log("Intentando login con:", username); // Debug

        let data = await login(username, password);
        console.log("Respuesta servidor:", data); // Debug

        if (data.exito) {
            // YA NO NECESITAS ESTO:
            // localStorage.setItem("actualProfile", ...); BORRAR
            alert("Login correcto. Redirigiendo...");

            // Simplemente rediriges. La cookie ya está grabada en el navegador.
            if (data.rol === "admin") {
                window.location.href = "opcAdmin.html";
            } else {
                window.location.href = "main.html";
            }
        } else {
            alert("Error: " + (data.error || "Credenciales incorrectas"));
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
        // Si el archivo PHP falla (Error 500, 404), lanzamos error
        if (!response.ok) return { exito: false, error: `Error del servidor (${response.status}). Ver consola.` };

        let data;
        try {
            data = await response.text();
            data = JSON.parse(data);
        } catch (e) {
            console.error("Error al parsear JSON:", e.message);
            throw new Error("Respuesta inválida del servidor: " + e.message);
        }

        return data;

    } catch (error) {
        console.error("Error en fetch:", error);
    }
}
