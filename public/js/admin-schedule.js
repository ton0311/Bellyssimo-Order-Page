document.addEventListener('DOMContentLoaded', () => {
    const orders = window.bellyssimoSchedule.orders;
    const orderById = new Map(orders.map((order) => [order.id, order]));
    const dialog = document.querySelector('#schedule-dialog');
    const money = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 });
    const scheduleFormat = new Intl.DateTimeFormat('en-PH', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });

    function escapeHtml(value) {
        const node = document.createElement('span');
        node.textContent = String(value ?? '');
        return node.innerHTML;
    }

    function openOrder(orderId) {
        const order = orderById.get(orderId);
        if (!order) return;

        const initials = order.customer.split(' ').map((part) => part[0]).slice(0, 2).join('').toUpperCase();
        document.querySelector('#schedule-order-id').textContent = `#${order.id}`;
        document.querySelector('#schedule-avatar').textContent = initials;
        document.querySelector('#schedule-customer').textContent = order.customer;
        document.querySelector('#schedule-phone').textContent = order.phone;
        document.querySelector('#schedule-phone').href = `tel:${order.phone.replace(/\s/g, '')}`;
        document.querySelector('#schedule-call').href = `tel:${order.phone.replace(/\s/g, '')}`;
        document.querySelector('#schedule-date').textContent = scheduleFormat.format(new Date(order.schedule));
        document.querySelector('#schedule-fulfillment').textContent = order.fulfillment;
        document.querySelector('#schedule-payment').textContent = `${money.format(order.total)} · ${order.payment_method} · ${order.payment}`;
        document.querySelector('#schedule-status').textContent = order.status;
        document.querySelector('#schedule-note').textContent = order.note;
        document.querySelector('#schedule-items').innerHTML = order.items.map((item) => `<div class="schedule-item">
            <img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.name)}">
            <div><strong>${escapeHtml(item.name)} (${escapeHtml(item.weight)})</strong><small>${escapeHtml(item.flavor)}</small></div>
            <b>${money.format(item.line_total)}</b>
        </div>`).join('');

        dialog.showModal();
    }

    document.addEventListener('click', (event) => {
        const orderButton = event.target.closest('[data-order-id]');
        if (orderButton) openOrder(orderButton.dataset.orderId);
    });

    document.querySelector('#close-schedule-dialog').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });

    const sidebar = document.querySelector('#sidebar');
    const backdrop = document.querySelector('#sidebar-backdrop');
    const menuButton = document.querySelector('#menu-button');

    function closeSidebar() {
        sidebar.classList.remove('open');
        backdrop.classList.remove('show');
        menuButton.setAttribute('aria-expanded', 'false');
    }

    menuButton.addEventListener('click', () => {
        const willOpen = !sidebar.classList.contains('open');
        sidebar.classList.toggle('open', willOpen);
        backdrop.classList.toggle('show', willOpen);
        menuButton.setAttribute('aria-expanded', String(willOpen));
    });
    backdrop.addEventListener('click', closeSidebar);
    sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeSidebar));
});
