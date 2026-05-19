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
        console.log("Respuesta CheckSession:", data);

        if (data.status.toLowerCase() === "success" && data.data && data.data.user) {
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
        location.reload();
    } catch (error) {
        console.error("Error al cerrar sesión:", error);
        location.reload();
    }
}