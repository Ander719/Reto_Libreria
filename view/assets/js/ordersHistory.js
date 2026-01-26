import { currentUser, checkSession, logout } from './session.js';

document.addEventListener("DOMContentLoaded", async () => {
    // 1. Verificar sesión
    const isLogged = await checkSession();
    
    if (!isLogged) {
        window.location.href = "login.html";
        return;
    }

    // 2. Mostrar nombre de usuario en el Header
    const welcomeLabel = document.getElementById('welcomeUser');
    if (welcomeLabel && currentUser) {
        // Usamos la propiedad name del objeto currentUser
        welcomeLabel.textContent = `Bienvenido, ${currentUser.name}`;
    }

    // 3. Configurar botón de logout
    const btnLogout = document.getElementById('btnLogout');
    if (btnLogout) {
        btnLogout.addEventListener('click', (e) => {
            e.preventDefault();
            logout();
        });
    }

    // 4. Cargar historial
    loadOrders(currentUser.profile_code);
});

async function loadOrders(profileCode) {
    const container = document.getElementById('ordersContainer');
    
    try {
        // Llamada a la API creada anteriormente
        const response = await fetch(`../../api/GetOrder.php?profileCode=${profileCode}`);
        const orders = await response.json();

        if (orders.length === 0) {
            container.innerHTML = "<p style='text-align:center;'>Aún no has realizado ninguna compra.</p>";
            return;
        }

        // Renderizado con diseño de tarjeta
        container.innerHTML = orders.map(order => `
            <div class="order-item">
                <img src="../assets/img/covers/${order.cover}" alt="${order.title}">
                <div class="order-details">
                    <h4>${order.title}</h4>
                    <p><strong>Fecha:</strong> ${order.date_buy}</p>
                    <p><strong>Cantidad:</strong> ${order.quantity}</p>
                    <p class="order-price">Total: ${(order.price * order.quantity).toFixed(2)}€</p>
                </div>
            </div>
        `).join('');
        
    } catch (error) {
        console.error("Error al cargar el historial:", error);
        container.innerHTML = "<p>Error al cargar el historial de compras.</p>";
    }
}