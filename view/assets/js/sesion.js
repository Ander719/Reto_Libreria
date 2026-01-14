// 1. Exportamos la variable (se inicializa en null)
export let currentUser = null;

// 2. Exportamos la función
export async function checkSession() {
    try {
        const response = await fetch('../../api/CheckSession.php', { credentials: 'include' });

        if (!response.ok) throw new Error("Error server");

        const data = await response.json();

        if (data.is_logged && data.user) {
            // Actualizamos la variable exportada
            currentUser = data.user;
            return true;
        }

        currentUser = null;
        return false;

    } catch (error) {
        console.error(error);
        currentUser = null;
        return false;
    }
}