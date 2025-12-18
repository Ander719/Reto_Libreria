document.addEventListener("DOMContentLoaded", () => {
  document
    .getElementById("loginForm")
    .addEventListener("submit", async function (e) {
      e.preventDefault();

      const username = document.getElementById("username").value;
      const password = document.getElementById("password").value;

      let data = await login(username, password);

      if (data) {
        if (data["error"]) {
          alert("El nombre de usuario o la contraseña son incorrectas.");
        } else if (data["resultado"]) {
          console.log("Login correcto, redirigiendo...");
          setTimeout(() => {
            window.location.href = "main.html";
          }
            , 1000);
        }
      } else {
        console.log("Error al cargar JSON.");
      }

    });

  async function login(username, password) {
    try {
      const response = await fetch(`../../api/Login.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ username, password }),
        // Credentials include es vital para que viaje la cookie si usas dominios distintos
        // pero en localhost ayuda a mantener la consistencia
        credentials: "include",
      });

      if (!response.ok) throw new Error("Error en la petición");
      return await response.json();
    } catch (error) {
      console.error(error);
      return null;
    }
  }
  /*
  const response = await fetch(`../../api/Login.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ username, password }),
      credentials: "include",
    });

    let data = await response.json();

    return data;
    
  */
});
