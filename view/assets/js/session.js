// 1. Exportamos la variable
export let currentUser = null;

// 2. Exportamos la función actualizada
export async function checkSession() {
    try {
        // Importante: credentials: 'include' para enviar la cookie PHPSESSID
        const response = await fetch('../../api/CheckSession.php', { credentials: 'include' });
        console.log("Status CheckSession:", response.status);
        if (!response.ok) throw new Error("Error server");

        const data = await response.json();

        // --- CAMBIO AQUÍ: Usamos el estándar "success" ---
        if (data.success && data.user) {
            currentUser = data.user; // Guardamos los datos en la variable global
            return true;
        }

        // Si success es false o no hay user
        currentUser = null;
        return false;

    } catch (error) {
        console.error("Error comprobando sesión:", error);
        currentUser = null;
        return false;
    }
}
export async function logout() {
    try {
        await fetch('../../api/Logout.php');
        console.log("Status Logout:", response.status);
        // No necesitamos comprobar respuesta, si falla la red, redirigimos igual por seguridad
        location.reload(); // Recargamos la página para actualizar el estado 
    } catch (error) {
        console.error("Error al cerrar sesión:", error);
        location.reload(); 
    }
}