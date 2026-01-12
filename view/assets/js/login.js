document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");

    if (loginForm) {
        loginForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            const username = document.getElementById("username").value;
            const password = document.getElementById("password").value;

            console.log("Intentando login con:", username); // Debug

            let data = await login(username, password);
            console.log("Respuesta servidor:", data); // Debug

            if (data) {
                // Caso 1: Error explícito del servidor
                if (data.error || data.exito === false) {
                    alert("Error: " + (data.error || "Credenciales incorrectas"));
                } 
                // Caso 2: Login exitoso (debe tener exito: true)
                else if (data.exito === true) {
                    // Guardamos datos para usarlos en el front
                    if (data.resultado) {
                        localStorage.setItem("actualProfile", JSON.stringify(data.resultado));
                    }
                    
                    alert("Login correcto. Redirigiendo...");

                    // Redirección
                    if (data.rol === "admin") {
                        window.location.href = "opcAdmin.html";
                    } else {
                        window.location.href = "main.html";
                    }
                } 
                // Caso 3: Respuesta inesperada
                else {
                    alert("Respuesta inesperada del servidor. Revisa la consola.");
                }
            }
        });
    }

    async function login(username, password) {
        try {
            const response = await fetch(`../../api/Login.php`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ username, password }),
            });

            // Si el archivo PHP falla (Error 500, 404), lanzamos error
            if (!response.ok) {
                const text = await response.text();
                console.error("Error red/servidor:", text);
                return { error: `Error del servidor (${response.status}). Ver consola.` };
            }

            return await response.json();
        } catch (error) {
            console.error("Error en fetch:", error);
            return { error: "No se pudo conectar con el servidor." };
        }
    }
});
