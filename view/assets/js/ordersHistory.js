import { checkSession, currentUser } from './session.js';
import { loadHeader, loadFooter } from './header.js';
import { apiFetch } from './apiClient.js';

document.addEventListener('DOMContentLoaded', async () => {

    // El historial solo tiene sentido para usuarios compradores.
    const isLogged = await checkSession();
    if (!isLogged || currentUser.role === 'admin') {
        window.location.replace('login.html');
        return;
    }

    await loadHeader("configProfile");
    await loadFooter();

    loadOrders();
});

/**
 * Carga y pinta los pedidos del usuario actual.
 *
 * @returns {Promise<void>}
 */
async function loadOrders() {
    const container = document.getElementById('ordersContainer');
    container.innerHTML = '<p class="loading-msg">Cargando tu historial...</p>';
    try {
        const payload = await apiFetch('../../api/GetOrder.php', { credentials: 'include' });
        console.log("Respuesta GetOrder:", payload);

        const orders = Array.isArray(payload.data) ? payload.data : [];

        if (!Array.isArray(orders) || orders.length === 0) {
            container.innerHTML = `
                <div class="no-orders">
                    <img src="../assets/img/book.svg" alt="Sin pedidos">
                    <h3>No tienes pedidos aún</h3>
                    <p>¡Visita la tienda y compra tu primer libro!</p>
                    <a href="main.html" class="btn">Ir a la Tienda</a>
                </div>`;
            return;
        }


        container.innerHTML = orders.map(order => {

            // OrderDao ya agrupa los libros dentro de cada pedido.
            const itemsHtml = order.items.map(item => `
                <div class="order-item">
                    <img src="../assets/img/covers/${item.cover}" alt="${item.title}" class="item-cover">
                    
                    <div class="item-details">
                        <h4 class="item-title">${item.title}</h4>
                        <p class="item-meta">
                            ${item.quantity} x ${parseFloat(item.price_unit).toFixed(2)}€
                        </p>
                        <p class="item-subtotal">
                            Subtotal: ${parseFloat(item.subtotal).toFixed(2)}€
                        </p>
                    </div>

                    <a href="bookDetails.html?isbn=${item.isbn}" class="btn-small">Ver Libro</a>
                </div>
            `).join('');

            return `
                <div class="order-card">
                    <div class="order-header">
                        <div class="header-left">
                            <span class="order-id">Pedido #${order.id_order}</span>
                            <span class="order-date">${new Date(order.date_buy).toLocaleString()}</span>
                        </div>
                        <div class="header-right">
                            <span class="status-badge completed">${order.status || 'Completado'}</span>
                            <span class="order-total">Total: ${parseFloat(order.total).toFixed(2)}€</span>
                        </div>
                    </div>
                    
                    <div class="order-body">
                        ${itemsHtml}
                    </div>
                </div>
            `;
        }).join('');

    } catch (error) {
        console.error("Error JS:", error);
        container.innerHTML = '<p class="error-msg">Error de conexión al cargar tus pedidos.</p>';
    }
}
