document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("loginForm").addEventListener("submit", async function (e) {
        e.preventDefault();

        const username = document.getElementById("username").value;
        const password = document.getElementById("password").value;

        // Llamada a la función
        let data = await login(username, password);

        if (data) {
            if (data.error) {
                alert("Error: " + data.error);
            } 
            else if (data.resultado) {
                // Guardamos los datos del usuario
                localStorage.setItem("actualProfile", JSON.stringify(data.resultado));

                console.log("Rol detectado:", data.rol); // Para depurar

                // LÓGICA DE REDIRECCIÓN INFALIBLE
                if (data.rol === "admin") {
                    window.location.href = "opcAdmin.html";
                } else {
                    window.location.href = "main.html";
                }
            }
        }
    });

    async function login(username, password) {
        try {
            // Asegúrate que esta ruta es correcta (../../api/Login.php si estás en view/html/)
            const response = await fetch(`../../api/Login.php`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ username, password }),
            });

            if (!response.ok) throw new Error("Error conexión");
            return await response.json();
        } catch (error) {
            console.error(error);
            return { error: "Error de conexión" };
        }
    }
});