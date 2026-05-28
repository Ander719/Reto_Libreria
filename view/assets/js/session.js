import { apiFetch } from './apiClient.js';

// Guarda el usuario que ha iniciado sesion
export let currentUser = null;

// Comprueba si el usuario tiene sesion activa
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

// Cierra sesion y recarga la pagina
export async function logout() {
    try {
        const response = await apiFetch('../../api/Logout.php', { method: 'POST', credentials: 'include' });
        console.log("Respuesta Logout:", response);
        // Aunque falle la peticion, la UI se recarga para no dejar estado autenticado obsoleto.
        location.reload();
    } catch (error) {
        console.error("Error al cerrar sesión:", error);
        location.reload(); 
    }
}
