import { checkSession, currentUser } from './session.js';
import { loadHeader, loadFooter } from './header.js';
import { apiFetch } from './apiClient.js';

document.addEventListener('DOMContentLoaded', async () => {

    //Si devuelve true, currentUser ya tendrá datos.
    const isLogged = await checkSession();
    if (!isLogged) {
        window.location.href = 'login.html';
        return;
    }

    if (currentUser && currentUser.role === 'admin') {
        window.location.href = 'main.html';
        return;
    }

    await loadHeader("configProfile");
    await loadFooter();

    loadOrders();
});

async function loadOrders() {
    const container = document.getElementById('ordersContainer');
    container.innerHTML = '<p class="loading-msg">Cargando tu historial...</p>';
    try {
        const payload = await apiFetch('../../api/GetOrder.php', { credentials: 'include' });
        console.log("Respuesta GetOrder:", payload);

        const ordersData = payload.data && payload.data.orders ? payload.data.orders : [];
        const orders = Array.isArray(ordersData) ? ordersData : [];

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

            const safeDate = new Date(order.date_buy).toLocaleString().replace(/&/g, "&amp;").replace(/</g, "&lt;");
            const safeStatus = (order.status || 'Completado').replace(/&/g, "&amp;").replace(/</g, "&lt;");
            const safeTotal = parseFloat(order.total).toFixed(2);

            const itemsHtml = order.items.map(item => {
                const safeCover = (item.cover || "").replace(/&/g, "&amp;").replace(/"/g, "&quot;");
                const safeTitle = (item.title || "").replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
                const safeIsbn = (item.isbn || "").replace(/"/g, "&quot;");
                const safePrice = parseFloat(item.price_unit).toFixed(2);
                const safeSubtotal = parseFloat(item.subtotal).toFixed(2);
                const safeQty = parseInt(item.quantity) || 1;
                return `
                <div class="order-item">
                    <img src="../assets/img/covers/${safeCover}" alt="${safeTitle}" class="item-cover">

                    <div class="item-details">
                        <h4 class="item-title">${safeTitle}</h4>
                        <p class="item-meta">
                            ${safeQty} x ${safePrice}€
                        </p>
                        <p class="item-subtotal">
                            Subtotal: ${safeSubtotal}€
                        </p>
                    </div>

                    <a href="bookDetails.html?isbn=${safeIsbn}" class="btn-small">Ver Libro</a>
                </div>
            `}).join('');

            return `
                <div class="order-card">
                    <div class="order-header">
                        <div class="header-left">
                            <span class="order-id">Pedido #${order.id_order}</span>
                            <span class="order-date">${safeDate}</span>
                        </div>
                        <div class="header-right">
                            <span class="status-badge completed">${safeStatus}</span>
                            <span class="order-total">Total: ${safeTotal}€</span>
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