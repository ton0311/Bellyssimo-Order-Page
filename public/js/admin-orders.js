document.addEventListener('DOMContentLoaded', () => {
    const initialOrders = window.bellyssimoAdmin.orders;
    const inventoryItems = window.bellyssimoAdmin.inventoryItems;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const money = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 });
    const scheduleFormat = new Intl.DateTimeFormat('en-PH', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
    const today = new Date().toLocaleDateString('en-CA');
    let orders = structuredClone(initialOrders);
    let selectedOrderId = orders[0]?.id ?? null;
    let activeStatus = 'all';

    const elements = {
        body: document.querySelector('#orders-body'),
        empty: document.querySelector('#empty-state'),
        resultCount: document.querySelector('#result-count'),
        search: document.querySelector('#order-search'),
        dateFilter: document.querySelector('#date-filter'),
        tabs: document.querySelector('#status-tabs'),
        inspector: document.querySelector('#order-inspector'),
        inspectorEmpty: document.querySelector('#inspector-empty'),
        inspectorContent: document.querySelector('#inspector-content'),
        statusSelect: document.querySelector('#status-select'),
        dialog: document.querySelector('#new-order-dialog'),
        form: document.querySelector('#new-order-form'),
        toast: document.querySelector('#toast'),
        inventoryDialog: document.querySelector('#inventory-dialog'),
        inventoryForm: document.querySelector('#inventory-form'),
        deleteDialog: document.querySelector('#delete-order-dialog'),
    };

    function escapeHtml(value) {
        const node = document.createElement('span');
        node.textContent = String(value ?? '');
        return node.innerHTML;
    }

    function formatSchedule(value) {
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return value;
        const datePart = value.slice(0, 10) === today ? 'Today' : scheduleFormat.format(date).split(',')[0];
        const timePart = new Intl.DateTimeFormat('en-PH', { hour: 'numeric', minute: '2-digit' }).format(date);
        return { date: datePart, time: timePart, full: `${datePart}, ${timePart}` };
    }

    function filteredOrders() {
        const query = elements.search.value.trim().toLowerCase();
        const dateFilter = elements.dateFilter.value;
        return orders.filter((order) => {
            const matchesStatus = activeStatus === 'all' || order.status === activeStatus;
            const haystack = `${order.id} ${order.customer} ${order.phone} ${order.email ?? ''} ${order.package}`.toLowerCase();
            const matchesSearch = !query || haystack.includes(query);
            const orderDate = order.schedule.slice(0, 10);
            const matchesDate = dateFilter === 'all' || (dateFilter === 'today' && orderDate === today) || (dateFilter === 'upcoming' && orderDate > today);
            return matchesStatus && matchesSearch && matchesDate;
        }).sort((a, b) => a.schedule.localeCompare(b.schedule));
    }

    function render() {
        const filtered = filteredOrders();
        elements.body.innerHTML = filtered.map((order) => {
            const schedule = formatSchedule(order.schedule);
            return `<tr data-order-id="${escapeHtml(order.id)}" class="${order.id === selectedOrderId ? 'selected' : ''}" tabindex="0">
                <td class="order-cell"><strong>#${escapeHtml(order.id)}</strong><span>${escapeHtml(order.package.match(/\(([^)]+)\)/)?.[1] ?? 'Belly')}</span></td>
                <td class="customer-cell"><strong>${escapeHtml(order.customer)}</strong><span>${escapeHtml(order.phone)}</span></td>
                <td class="schedule-cell"><strong>${escapeHtml(schedule.date)}</strong><span>${escapeHtml(schedule.time)}</span></td>
                <td class="amount">${money.format(order.total)}</td>
                <td><span class="status-badge" data-status="${escapeHtml(order.status)}">${escapeHtml(order.status)}</span></td>
                <td><button class="row-open" aria-label="Open ${escapeHtml(order.id)}"><span class="material-symbols-rounded">chevron_right</span></button></td>
            </tr>`;
        }).join('');

        elements.empty.hidden = filtered.length !== 0;
        elements.resultCount.textContent = `${filtered.length} ${filtered.length === 1 ? 'record' : 'records'}`;
        updateCounts();
        renderInspector();
    }

    function updateCounts() {
        const counts = orders.reduce((summary, order) => {
            summary[order.status] = (summary[order.status] ?? 0) + 1;
            return summary;
        }, {});
        document.querySelector('#nav-order-count').textContent = orders.length;
        document.querySelector('#metric-attention').textContent = counts['Pending confirmation'] ?? 0;
        document.querySelector('#metric-confirmed').textContent = counts['Confirmed'] ?? 0;
        document.querySelector('#metric-cancelled').textContent = counts['Cancelled'] ?? 0;
        const completedPaid = orders.filter((order) => order.status === 'Completed' && order.payment === 'Paid').reduce((sum, order) => sum + order.total, 0);
        document.querySelector('#metric-sales').textContent = money.format(completedPaid);
        elements.tabs.querySelectorAll('button').forEach((button) => {
            const count = button.dataset.status === 'all' ? orders.length : (counts[button.dataset.status] ?? 0);
            button.querySelector('span').textContent = count;
        });
    }

    function renderInspector() {
        const order = orders.find((item) => item.id === selectedOrderId);
        elements.inspectorEmpty.hidden = Boolean(order);
        elements.inspectorContent.hidden = !order;
        if (!order) return;

        const schedule = formatSchedule(order.schedule);
        const initials = order.customer.split(' ').map((part) => part[0]).slice(0, 2).join('').toUpperCase();
        const status = document.querySelector('#inspector-status');
        status.textContent = order.status;
        status.dataset.status = order.status;
        document.querySelector('#inspector-id').textContent = `#${order.id}`;
        document.querySelector('#inspector-created').textContent = order.created;
        document.querySelector('#customer-avatar').textContent = initials;
        document.querySelector('#customer-name').textContent = order.customer;
        document.querySelector('#customer-phone').textContent = order.phone;
        document.querySelector('#customer-phone').href = `tel:${order.phone.replace(/\s/g, '')}`;
        const customerEmail = document.querySelector('#customer-email');
        customerEmail.textContent = order.email ?? '';
        customerEmail.hidden = !order.email;
        if (order.email) {
            customerEmail.href = `mailto:${order.email}`;
        } else {
            customerEmail.removeAttribute('href');
        }
        document.querySelector('#call-customer').href = `tel:${order.phone.replace(/\s/g, '')}`;
        document.querySelector('#order-items').innerHTML = order.items.map((item) => `<div class="product-line">
            <img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.name)}">
            <div><strong>${escapeHtml(item.name)} (${escapeHtml(item.weight)})</strong><span>${escapeHtml(item.flavor)}</span></div>
            <strong>${money.format(item.line_total)}</strong>
        </div>`).join('');
        document.querySelector('#order-total').textContent = money.format(order.total);
        document.querySelector('#order-schedule').textContent = schedule.full;
        document.querySelector('#order-fulfillment').textContent = order.fulfillment;
        document.querySelector('#order-payment').textContent = `${order.payment_method} · ${order.payment}`;
        document.querySelector('#order-note').textContent = order.note || 'No customer note.';
        elements.statusSelect.value = order.status;
    }

    function selectOrder(id) {
        selectedOrderId = id;
        render();
        if (window.matchMedia('(max-width: 1180px)').matches) elements.inspector.classList.add('open');
    }

    function showToast(message) {
        document.querySelector('#toast-message').textContent = message;
        elements.toast.classList.add('show');
        window.clearTimeout(showToast.timeout);
        showToast.timeout = window.setTimeout(() => elements.toast.classList.remove('show'), 2400);
    }

    elements.body.addEventListener('click', (event) => {
        const row = event.target.closest('tr[data-order-id]');
        if (row) selectOrder(row.dataset.orderId);
    });

    elements.body.addEventListener('keydown', (event) => {
        const row = event.target.closest('tr[data-order-id]');
        if (row && (event.key === 'Enter' || event.key === ' ')) {
            event.preventDefault();
            selectOrder(row.dataset.orderId);
        }
    });

    elements.search.addEventListener('input', render);
    elements.dateFilter.addEventListener('change', render);
    elements.tabs.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-status]');
        if (!button) return;
        activeStatus = button.dataset.status;
        elements.tabs.querySelectorAll('button').forEach((item) => item.classList.toggle('active', item === button));
        render();
    });

    document.querySelector('#save-status').addEventListener('click', async () => {
        const order = orders.find((item) => item.id === selectedOrderId);
        if (!order) return;
        const button = document.querySelector('#save-status');
        button.disabled = true;

        try {
            const url = window.bellyssimoAdmin.statusUrlTemplate.replace('__ORDER__', order.database_id);
            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ status: elements.statusSelect.value }),
            });

            if (!response.ok) throw new Error('Unable to update this order right now.');
            const payload = await response.json();
            orders = orders.map((item) => item.database_id === payload.order.database_id ? payload.order : item);
            render();
            showToast(payload.message);
            window.setTimeout(() => window.location.reload(), 450);
        } catch (error) {
            showToast(error.message);
        } finally {
            button.disabled = false;
        }
    });

    document.querySelector('#delete-order').addEventListener('click', () => {
        const order = orders.find((item) => item.id === selectedOrderId);
        if (!order) return;
        document.querySelector('#delete-order-reference').textContent = `#${order.id} · ${order.customer}`;
        elements.deleteDialog.showModal();
    });
    document.querySelector('#close-delete-dialog').addEventListener('click', () => elements.deleteDialog.close());
    document.querySelector('#cancel-delete-order').addEventListener('click', () => elements.deleteDialog.close());
    elements.deleteDialog.addEventListener('click', (event) => {
        if (event.target === elements.deleteDialog) elements.deleteDialog.close();
    });
    document.querySelector('#confirm-delete-order').addEventListener('click', async () => {
        const order = orders.find((item) => item.id === selectedOrderId);
        if (!order) return;
        const button = document.querySelector('#confirm-delete-order');
        button.disabled = true;

        try {
            const url = window.bellyssimoAdmin.deleteUrlTemplate.replace('__ORDER__', order.database_id);
            const response = await fetch(url, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message ?? 'Unable to delete this order right now.');

            orders = orders.filter((item) => item.database_id !== order.database_id);
            selectedOrderId = orders[0]?.id ?? null;
            elements.deleteDialog.close();
            elements.inspector.classList.remove('open');
            render();
            showToast(payload.message);
            window.setTimeout(() => window.location.reload(), 650);
        } catch (error) {
            showToast(error.message);
        } finally {
            button.disabled = false;
        }
    });

    document.querySelector('#new-order-button').addEventListener('click', () => elements.dialog.showModal());
    document.querySelector('#close-dialog').addEventListener('click', () => elements.dialog.close());
    document.querySelector('#cancel-dialog').addEventListener('click', () => elements.dialog.close());
    elements.dialog.addEventListener('click', (event) => {
        if (event.target === elements.dialog) elements.dialog.close();
    });

    const fulfillmentSelect = document.querySelector('#manual-fulfillment');
    const deliveryLocation = document.querySelector('#manual-delivery-location');
    fulfillmentSelect.addEventListener('change', () => {
        const isDelivery = fulfillmentSelect.value === 'Delivery';
        deliveryLocation.hidden = !isDelivery;
        deliveryLocation.querySelector('input').required = isDelivery;
    });

    elements.form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const data = new FormData(elements.form);
        const submitButton = elements.form.querySelector('[type="submit"]');
        submitButton.disabled = true;

        try {
            const response = await fetch(window.bellyssimoAdmin.storeUrl, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: data,
            });
            const payload = await response.json();
            if (!response.ok) {
                const firstError = Object.values(payload.errors ?? {})[0]?.[0];
                throw new Error(firstError ?? 'Unable to add this order right now.');
            }

            orders.unshift(payload.order);
            selectedOrderId = payload.order.id;
            elements.form.reset();
            deliveryLocation.hidden = true;
            deliveryLocation.querySelector('input').required = false;
            elements.dialog.close();
            render();
            if (window.matchMedia('(max-width: 1180px)').matches) elements.inspector.classList.add('open');
            showToast(payload.message);
            window.location.hash = 'orders';
            window.setTimeout(() => window.location.reload(), 450);
        } catch (error) {
            showToast(error.message);
        } finally {
            submitButton.disabled = false;
        }
    });

    document.querySelector('#export-button').addEventListener('click', () => {
        const headers = ['Order ID','Customer','Phone','Email','Package','Flavor','Schedule','Fulfillment','Payment Method','Payment Status','Total','Status','Note'];
        const rows = orders.map((order) => [order.id, order.customer, order.phone, order.email ?? '', order.package, order.flavor, order.schedule, order.fulfillment, order.payment_method, order.payment, order.total, order.status, order.note]);
        const csv = [headers, ...rows].map((row) => row.map((value) => `"${String(value).replaceAll('"', '""')}"`).join(',')).join('\n');
        const link = document.createElement('a');
        link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
        link.download = `bellyssimo-orders-${today}.csv`;
        link.click();
        URL.revokeObjectURL(link.href);
        showToast('Orders exported as CSV');
    });

    document.querySelector('#close-inspector').addEventListener('click', () => elements.inspector.classList.remove('open'));

    const allowedSections = ['overview', 'orders', 'customers', 'inventory', 'reports'];
    function showSection() {
        const requested = window.location.hash.slice(1);
        const section = allowedSections.includes(requested) ? requested : 'overview';
        document.querySelectorAll('[data-admin-section]').forEach((panel) => {
            panel.hidden = panel.dataset.adminSection !== section;
        });
        document.querySelectorAll('.nav-list [data-section-link]').forEach((link) => {
            const isActive = link.dataset.sectionLink === section;
            link.classList.toggle('active', isActive);
            if (isActive) link.setAttribute('aria-current', 'page');
            else link.removeAttribute('aria-current');
        });
        document.title = `${section[0].toUpperCase()}${section.slice(1)} | Toño's Bellyssimo Admin`;
    }

    window.addEventListener('hashchange', showSection);

    const inventoryDialogTitle = document.querySelector('#inventory-dialog-title');
    function openInventoryDialog(item = null) {
        elements.inventoryForm.reset();
        elements.inventoryForm.elements.id.value = item?.id ?? '';
        inventoryDialogTitle.textContent = item ? 'Edit inventory item' : 'Add inventory item';

        if (item) {
            ['name', 'category', 'quantity', 'unit', 'low_stock_threshold', 'unit_cost', 'notes'].forEach((field) => {
                elements.inventoryForm.elements[field].value = item[field] ?? '';
            });
        }

        elements.inventoryDialog.showModal();
    }

    document.querySelector('#new-inventory-button').addEventListener('click', () => openInventoryDialog());
    document.querySelectorAll('[data-inventory-edit]').forEach((button) => button.addEventListener('click', () => {
        const item = inventoryItems.find((entry) => String(entry.id) === button.dataset.inventoryEdit);
        if (item) openInventoryDialog(item);
    }));
    document.querySelector('#close-inventory-dialog').addEventListener('click', () => elements.inventoryDialog.close());
    document.querySelector('#cancel-inventory-dialog').addEventListener('click', () => elements.inventoryDialog.close());
    elements.inventoryDialog.addEventListener('click', (event) => {
        if (event.target === elements.inventoryDialog) elements.inventoryDialog.close();
    });
    elements.inventoryForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const data = new FormData(elements.inventoryForm);
        const id = data.get('id');
        data.delete('id');
        const submitButton = elements.inventoryForm.querySelector('[type="submit"]');
        submitButton.disabled = true;

        try {
            const url = id
                ? window.bellyssimoAdmin.inventoryUpdateUrlTemplate.replace('__ITEM__', id)
                : window.bellyssimoAdmin.inventoryStoreUrl;
            if (!data.get('unit_cost')) data.delete('unit_cost');
            const response = await fetch(url, {
                method: id ? 'PATCH' : 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: data,
            });
            const payload = await response.json();
            if (!response.ok) {
                const firstError = Object.values(payload.errors ?? {})[0]?.[0];
                throw new Error(firstError ?? 'Unable to save this inventory item.');
            }
            elements.inventoryDialog.close();
            showToast(payload.message);
            window.location.hash = 'inventory';
            window.setTimeout(() => window.location.reload(), 450);
        } catch (error) {
            showToast(error.message);
        } finally {
            submitButton.disabled = false;
        }
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

    showSection();
    render();
});
