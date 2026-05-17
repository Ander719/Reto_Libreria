import { apiFetch } from './apiClient.js';

// 1. Exportamos la variable
export let currentUser = null;

// 2. Exportamos la función actualizada
export async function checkSession() {
    try {
        const data = await apiFetch('../../api/CheckSession.php', {
            credentials: 'include',
            allowedStatuses: [401]
        });

        if (data.status.toLowerCase() === "success" && data.data && data.data.user) {
            console.log("Respuesta CheckSession:", data);
            currentUser = data.data.user;
            return true;
        }

        currentUser = null;
        return false;

    } catch (error) {
        console.error("Fallo crítico real comprobando sesión:", error);
        currentUser = null;
        return false;
    }
}
export async function logout() {
    try {
        const response = await apiFetch('../../api/Logout.php', { method: 'POST', credentials: 'include' });
        console.log("Respuesta Logout:", response);
        // No necesitamos comprobar respuesta, si falla la red, redirigimos igual por seguridad
        location.reload(); // Recargamos la página para actualizar el estado 
    } catch (error) {
        console.error("Error al cerrar sesión:", error);
        location.reload(); 
    }
}
