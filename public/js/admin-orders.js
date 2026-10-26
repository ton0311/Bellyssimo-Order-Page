document.addEventListener('DOMContentLoaded', () => {
    const config = window.bellyssimoAdmin;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const money = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 });
    const scheduleFormat = new Intl.DateTimeFormat('en-PH', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
    const timeFormat = new Intl.DateTimeFormat('en-PH', { hour: 'numeric', minute: '2-digit' });
    const today = new Date().toLocaleDateString('en-CA', { timeZone: 'Asia/Manila' });
    let orders = structuredClone(config.orders);
    let inventoryItems = structuredClone(config.inventoryItems);
    let ordersPagination = { ...config.ordersPagination };
    let customersPagination = { current_page: 1, last_page: 1, total: 0 };
    let dashboard = { ...config.dashboard };
    let selectedOrderId = orders[0]?.id ?? null;
    let activeStatus = 'all';
    let selectedCustomerId = null;
    let customersLoaded = false;
    let reportsLoaded = false;
    let orderSearchTimer = null;
    let customerSearchTimer = null;
    let ordersRequestId = 0;
    let customersRequestId = 0;
    const inventoryMoney = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const chartMoney = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 1, maximumFractionDigits: 1 });

    const elements = {
        body: document.querySelector('#orders-body'),
        empty: document.querySelector('#empty-state'),
        resultCount: document.querySelector('#result-count'),
        search: document.querySelector('#order-search'),
        dateFilter: document.querySelector('#date-filter'),
        tabs: document.querySelector('#status-tabs'),
        ordersPrev: document.querySelector('#orders-prev'),
        ordersNext: document.querySelector('#orders-next'),
        ordersPage: document.querySelector('#orders-page'),
        customersBody: document.querySelector('#customers-body'),
        customerSearch: document.querySelector('#customer-search'),
        customersPrev: document.querySelector('#customers-prev'),
        customersNext: document.querySelector('#customers-next'),
        customersPage: document.querySelector('#customers-page'),
        inventoryBody: document.querySelector('#inventory-body'),
        inspector: document.querySelector('#order-inspector'),
        inspectorEmpty: document.querySelector('#inspector-empty'),
        inspectorContent: document.querySelector('#inspector-content'),
        statusSelect: document.querySelector('#status-select'),
        dialog: document.querySelector('#new-order-dialog'),
        form: document.querySelector('#new-order-form'),
        editDialog: document.querySelector('#edit-order-dialog'),
        editForm: document.querySelector('#edit-order-form'),
        toast: document.querySelector('#toast'),
        inventoryDialog: document.querySelector('#inventory-dialog'),
        inventoryForm: document.querySelector('#inventory-form'),
        deleteDialog: document.querySelector('#delete-order-dialog'),
        paymentForm: document.querySelector('#payment-form'),
        deleteCustomerDialog: document.querySelector('#delete-customer-dialog'),
    };

    function escapeHtml(value) {
        const node = document.createElement('span');
        node.textContent = String(value ?? '');
        return node.innerHTML.replaceAll('"', '&quot;').replaceAll("'", '&#39;');
    }

    function formatSchedule(value) {
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return { date: value, time: '', full: value };
        const datePart = value.slice(0, 10) === today ? 'Today' : scheduleFormat.format(date).split(',')[0];
        const timePart = timeFormat.format(date);
        return { date: datePart, time: timePart, full: datePart + ', ' + timePart };
    }

    async function jsonRequest(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Accept': 'application/json',
                ...(options.headers ?? {}),
            },
        });
        const payload = await response.json();
        if (!response.ok) {
            const firstError = Object.values(payload.errors ?? {})[0]?.[0];
            throw new Error(firstError ?? payload.message ?? 'Unable to load dashboard data.');
        }
        return payload;
    }

    function render() {
        elements.body.innerHTML = orders.map((order) => {
            const schedule = formatSchedule(order.schedule);
            const weight = order.package.match(/\(([^)]+)\)/)?.[1] ?? 'Belly';
            return '<tr data-order-id="' + escapeHtml(order.id) + '" class="' + (order.id === selectedOrderId ? 'selected' : '') + '" tabindex="0">' +
                '<td class="order-cell"><strong>#' + escapeHtml(order.id) + '</strong><span>' + escapeHtml(weight) + '</span></td>' +
                '<td class="customer-cell"><strong>' + escapeHtml(order.customer) + '</strong><span>' + escapeHtml(order.phone) + '</span></td>' +
                '<td class="schedule-cell"><strong>' + escapeHtml(schedule.date) + '</strong><span>' + escapeHtml(schedule.time) + '</span></td>' +
                '<td class="amount">' + money.format(order.total) + '</td>' +
                '<td><span class="status-badge" data-status="' + escapeHtml(order.status) + '">' + escapeHtml(order.status) + '</span></td>' +
                '<td><button class="row-open" aria-label="Open ' + escapeHtml(order.id) + '"><span class="material-symbols-rounded">chevron_right</span></button></td>' +
                '</tr>';
        }).join('');

        elements.empty.hidden = orders.length !== 0;
        elements.resultCount.textContent = ordersPagination.total + ' ' + (ordersPagination.total === 1 ? 'record' : 'records');
        elements.ordersPage.textContent = 'Page ' + ordersPagination.current_page + ' of ' + ordersPagination.last_page;
        elements.ordersPrev.disabled = ordersPagination.current_page <= 1;
        elements.ordersNext.disabled = ordersPagination.current_page >= ordersPagination.last_page;
        renderInspector();
    }

    function updateSummaryDisplay() {
        const setText = (selector, value) => {
            const node = document.querySelector(selector);
            if (node) node.textContent = value;
        };
        setText('#nav-order-count', dashboard.total_orders);
        setText('#metric-attention', dashboard.pending);
        setText('#metric-confirmed', dashboard.confirmed);
        setText('#metric-not-proceeding', Number(dashboard.declined) + Number(dashboard.cancelled));
        setText('#metric-sales', money.format(dashboard.total_revenue));
        setText('#inventory-low-count', dashboard.low_stock_count);
        setText('#overview-pending', dashboard.pending);
        setText('#overview-upcoming', dashboard.upcoming_confirmed);
        setText('#overview-low-stock', dashboard.low_stock_count);
        setText('#overview-month-revenue', money.format(dashboard.month_revenue));
        setText('#overview-total-revenue', money.format(dashboard.total_revenue));
        setText('#attention-pending', dashboard.pending + ' pending requests');
        setText('#attention-overdue', dashboard.overdue_pending + ' overdue requests');
        setText('#attention-low-stock', dashboard.low_stock_count + ' low-stock items');
        setText('#report-total-revenue', money.format(dashboard.total_revenue));
        setText('#report-month-revenue', money.format(dashboard.month_revenue));
        setText('#report-completed', dashboard.completed);
        setText('#report-average-order', money.format(dashboard.average_order));
        setText('#report-total-orders', dashboard.total_orders);

        elements.tabs.querySelectorAll('button').forEach((button) => {
            const count = button.dataset.status === 'all'
                ? dashboard.total_orders
                : (dashboard.status_counts[button.dataset.status] ?? 0);
            button.querySelector('span').textContent = count;
        });
        document.querySelectorAll('[data-dashboard-status-count]').forEach((node) => {
            node.textContent = dashboard.status_counts[node.dataset.dashboardStatusCount] ?? 0;
        });

        const overviewChart = document.querySelector('#overview-monthly-chart');
        if (overviewChart) {
            overviewChart.innerHTML = dashboard.monthly.map((month) =>
                '<div class="bar-column"><span class="bar-value">' + chartMoney.format(month.revenue / 1000) + 'k</span>' +
                '<i style="height:' + month.height + '%"></i><small>' + escapeHtml(month.label) + '</small></div>'
            ).join('');
        }
        const reportChart = document.querySelector('#report-monthly-chart');
        if (reportChart) {
            reportChart.innerHTML = dashboard.monthly.map((month) =>
                '<div class="bar-column"><span class="bar-value">' + chartMoney.format(month.revenue / 1000) + 'k</span>' +
                '<i style="height:' + month.height + '%"></i><small>' + escapeHtml(month.label) + ' · ' + month.orders + '</small></div>'
            ).join('');
        }
    }
    async function fetchOrders(page = 1) {
        const requestId = ++ordersRequestId;
        const url = new URL(config.ordersDataUrl, window.location.origin);
        url.searchParams.set('page', page);
        const query = elements.search.value.trim();
        if (query) url.searchParams.set('q', query);
        if (activeStatus !== 'all') url.searchParams.set('status', activeStatus);
        if (elements.dateFilter.value !== 'all') url.searchParams.set('date', elements.dateFilter.value);

        elements.body.setAttribute('aria-busy', 'true');
        try {
            const payload = await jsonRequest(url);
            if (requestId !== ordersRequestId) return;
            if (payload.pagination.current_page > payload.pagination.last_page) {
                return fetchOrders(payload.pagination.last_page);
            }
            orders = payload.orders;
            ordersPagination = payload.pagination;
            if (!orders.some((order) => order.id === selectedOrderId)) {
                selectedOrderId = orders[0]?.id ?? null;
            }
            render();
        } catch (error) {
            if (requestId === ordersRequestId) showToast(error.message);
        } finally {
            if (requestId === ordersRequestId) elements.body.removeAttribute('aria-busy');
        }
    }

    async function refreshSummary() {
        try {
            const payload = await jsonRequest(config.summaryUrl);
            dashboard = payload.dashboard;
            updateSummaryDisplay();
            document.querySelector('#customer-count').textContent = payload.customer_count;
            document.querySelector('#inventory-count').textContent = payload.inventory_count;
        } catch (error) {
            showToast(error.message);
        }
    }

    function renderCustomers(customers) {
        elements.customersBody.innerHTML = customers.length
            ? customers.map((customer) => '<tr>' +
                '<td><strong>' + escapeHtml(customer.name) + '</strong></td>' +
                '<td><a class="table-link" href="tel:' + escapeHtml(String(customer.phone).replace(/\D/g, '')) + '">' + escapeHtml(customer.phone) + '</a></td>' +
                '<td>' + (customer.email ? '<a class="table-link" href="mailto:' + escapeHtml(customer.email) + '">' + escapeHtml(customer.email) + '</a>' : '<span class="cell-note">Not provided</span>') + '</td>' +
                '<td>' + customer.orders + '</td><td>' + customer.completed_orders + '</td>' +
                '<td class="amount">' + money.format(customer.lifetime_value) + '</td>' +
                '<td>' + escapeHtml(customer.last_order) + '</td>' +
                '<td><button class="row-action customer-delete" type="button" data-customer-delete="' + customer.database_id + '" data-customer-name="' + escapeHtml(customer.name) + '"><span class="material-symbols-rounded">delete</span>Delete</button></td>' +
                '</tr>').join('')
            : '<tr><td colspan="8"><div class="empty-state"><span class="material-symbols-rounded">group_off</span><h3>No matching customers</h3><p>Try a different customer search.</p></div></td></tr>';
        elements.customersPage.textContent = 'Page ' + customersPagination.current_page + ' of ' + customersPagination.last_page;
        elements.customersPrev.disabled = customersPagination.current_page <= 1;
        elements.customersNext.disabled = customersPagination.current_page >= customersPagination.last_page;
    }

    async function fetchCustomers(page = 1) {
        const requestId = ++customersRequestId;
        const url = new URL(config.customersDataUrl, window.location.origin);
        url.searchParams.set('page', page);
        const query = elements.customerSearch.value.trim();
        if (query) url.searchParams.set('q', query);
        elements.customersBody.innerHTML = '<tr><td colspan="8" class="table-loading">Loading customer records...</td></tr>';

        try {
            const payload = await jsonRequest(url);
            if (requestId !== customersRequestId) return;
            if (payload.pagination.current_page > payload.pagination.last_page) {
                return fetchCustomers(payload.pagination.last_page);
            }
            customersPagination = payload.pagination;
            renderCustomers(payload.customers);
            customersLoaded = true;
        } catch (error) {
            if (requestId !== customersRequestId) return;
            customersLoaded = false;
            elements.customersBody.innerHTML = '<tr><td colspan="8" class="table-loading">' + escapeHtml(error.message) + '</td></tr>';
        }
    }

    async function loadReports() {
        if (reportsLoaded) return;
        try {
            const payload = await jsonRequest(config.reportsDataUrl);
            document.querySelector('#package-mix').innerHTML = payload.package_mix.map((item) =>
                '<div><span>' + escapeHtml(item.weight) + '</span><i><b style="width:' + item.width + '%"></b></i><strong>' + item.count + '</strong></div>'
            ).join('');
            reportsLoaded = true;
        } catch (error) {
            document.querySelector('#package-mix').innerHTML = '<p class="table-loading">' + escapeHtml(error.message) + '</p>';
        }
    }

    function renderInventory() {
        elements.inventoryBody.innerHTML = inventoryItems.length
            ? inventoryItems.map((item) => {
                const isLow = Number(item.quantity) <= Number(item.low_stock_threshold);
                const quantity = Number(item.quantity).toLocaleString('en-PH', { maximumFractionDigits: 2 });
                const threshold = Number(item.low_stock_threshold).toLocaleString('en-PH', { maximumFractionDigits: 2 });
                return '<tr><td><strong>' + escapeHtml(item.name) + '</strong>' + (item.notes ? '<span class="cell-note">' + escapeHtml(item.notes) + '</span>' : '') + '</td>' +
                    '<td>' + escapeHtml(item.category) + '</td><td class="amount">' + quantity + ' ' + escapeHtml(item.unit) + '</td>' +
                    '<td>' + threshold + ' ' + escapeHtml(item.unit) + '</td><td>' + (item.unit_cost === null ? '-' : inventoryMoney.format(item.unit_cost)) + '</td>' +
                    '<td><span class="stock-badge ' + (isLow ? 'low' : 'good') + '">' + (isLow ? 'Low stock' : 'In stock') + '</span></td>' +
                    '<td><button class="row-action" data-inventory-edit="' + item.id + '"><span class="material-symbols-rounded">edit</span>Edit</button></td></tr>';
            }).join('')
            : '<tr><td colspan="7"><div class="empty-state"><span class="material-symbols-rounded">inventory_2</span><h3>No inventory items</h3><p>Add your first kitchen item.</p></div></td></tr>';
        document.querySelector('#inventory-count').textContent = inventoryItems.length;
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
        const riderDraftPanel = document.querySelector('#rider-draft-panel');
        const isDelivery = order.fulfillment_method === 'Delivery';
        riderDraftPanel.hidden = !isDelivery;
        document.querySelector('#rider-draft').value = isDelivery ? riderDraft(order, schedule.full) : '';
        document.querySelector('#payment-amount-paid').textContent = money.format(order.amount_paid);
        document.querySelector('#payment-balance').textContent = money.format(order.remaining_balance);
        document.querySelector('#payment-balance-row').hidden = Number(order.amount_paid) <= 0;
        const paymentInput = document.querySelector('#amount-paid-input');
        paymentInput.max = order.total;
        paymentInput.value = order.amount_paid;
        paymentInput.disabled = order.status === 'Completed';
        elements.paymentForm.querySelector('[type="submit"]').disabled = order.status === 'Completed';
        document.querySelector('#payment-help').textContent = order.status === 'Completed'
            ? 'Completed orders are fully paid and locked.'
            : 'Enter the total amount received so far. It will be included in sales.';
        elements.statusSelect.value = order.status;
    }

    function riderDraft(order, formattedSchedule) {
        const isDelivery = order.fulfillment_method === 'Delivery';
        const itemLines = order.items.map((item) => `- ${item.name} (${item.weight}) · ${item.flavor}`).join('\n');
        const handoffLine = isDelivery
            ? `Drop-off: ${order.delivery_location || 'Address to be confirmed'}`
            : 'Handoff: Customer pickup at Bellyssimo';
        const balanceLine = Number(order.remaining_balance) > 0
            ? `Amount to collect: ${money.format(order.remaining_balance)}`
            : 'Amount to collect: Fully paid';
        const notes = order.note_raw?.trim() || 'None';

        return `BELLYSSIMO ${isDelivery ? 'DELIVERY' : 'PICKUP'} DETAILS

Order: #${order.id}
Pickup point: Bellyssimo, Perayra Village, Maningcol
Pickup contact: ${window.bellyssimoAdmin.businessContact}
${handoffLine}

Customer: ${order.customer}
Contact: ${order.phone}
Schedule: ${formattedSchedule}

Items:
${itemLines}

Order total: ${money.format(order.total)}
Payment: ${order.payment_method} · ${order.payment}
${balanceLine}

Notes: ${notes}`;
    }

    function selectOrder(id) {
        const previousRow = elements.body.querySelector('tr.selected');
        previousRow?.classList.remove('selected');
        selectedOrderId = id;
        elements.body.querySelector('tr[data-order-id="' + CSS.escape(id) + '"]')?.classList.add('selected');
        renderInspector();
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

    elements.search.addEventListener('input', () => {
        ++ordersRequestId;
        window.clearTimeout(orderSearchTimer);
        orderSearchTimer = window.setTimeout(() => fetchOrders(1), 250);
    });
    elements.dateFilter.addEventListener('change', () => fetchOrders(1));
    elements.tabs.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-status]');
        if (!button) return;
        activeStatus = button.dataset.status;
        elements.tabs.querySelectorAll('button').forEach((item) => item.classList.toggle('active', item === button));
        fetchOrders(1);
    });
    elements.ordersPrev.addEventListener('click', () => fetchOrders(ordersPagination.current_page - 1));
    elements.ordersNext.addEventListener('click', () => fetchOrders(ordersPagination.current_page + 1));

    document.querySelector('#copy-rider-draft').addEventListener('click', async () => {
        const draft = document.querySelector('#rider-draft');
        const text = draft.value.trim();
        if (!text) return;

        try {
            await navigator.clipboard.writeText(text);
            showToast('Rider details copied.');
        } catch {
            draft.focus();
            draft.select();
            const copied = document.execCommand('copy');
            draft.setSelectionRange(0, 0);
            if (copied) {
                showToast('Rider details copied.');
            } else {
                showToast('Copy failed. Select the draft and copy it manually.');
            }
        }
    });

    const editFulfillmentSelect = document.querySelector('#edit-fulfillment');
    const editDeliveryLocation = document.querySelector('#edit-delivery-location');
    const editItemsContainer = document.querySelector('#edit-order-items');
    const packages = window.bellyssimoAdmin.packages ?? {};
    let editItems = [];

    function renderEditItems() {
        editItemsContainer.innerHTML = editItems.map((item, index) => `<div class="edit-item-row" data-edit-item="${index}">
            <label>Belly ${index + 1} package<select name="items[${index}][package]" data-item-field="package" required>${Object.entries(packages).map(([key, product]) => `<option value="${escapeHtml(key)}" ${key === item.package ? 'selected' : ''}>${escapeHtml(product.weight)} · ${escapeHtml(product.name)} · ${money.format(product.price)}</option>`).join('')}</select></label>
            <label>Flavor<select name="items[${index}][flavor]" data-item-field="flavor" required><option value="Regular" ${item.flavor === 'Regular' ? 'selected' : ''}>Regular</option><option value="Spicy" ${item.flavor === 'Spicy' ? 'selected' : ''}>Spicy (+₱50)</option></select></label>
            <button class="icon-button" data-remove-edit-item="${index}" type="button" aria-label="Remove belly ${index + 1}" ${editItems.length === 1 ? 'disabled' : ''}><span class="material-symbols-rounded">delete</span></button>
        </div>`).join('');
        updateEditTotal();
    }

    function updateEditTotal() {
        const order = orders.find((item) => item.id === selectedOrderId);
        const unchanged = order && editItems.length === order.items.length && editItems.every((item, index) => item.package === order.items[index].package_key && item.flavor === order.items[index].flavor);
        const total = unchanged ? order.total : editItems.reduce((sum, item) => sum + (packages[item.package]?.price ?? 0) + (item.flavor === 'Spicy' ? 50 : 0), 0);
        document.querySelector('#edit-belly-count').textContent = `${editItems.length} ${editItems.length === 1 ? 'belly' : 'bellies'}`;
        document.querySelector('#edit-order-total').textContent = money.format(total);
        document.querySelector('#add-edit-item').disabled = editItems.length >= 20;
    }

    document.querySelector('#add-edit-item').addEventListener('click', () => {
        if (editItems.length >= 20) return;
        editItems.push({ package: 'barkadahan', flavor: 'Regular' });
        renderEditItems();
    });
    editItemsContainer.addEventListener('change', (event) => {
        const row = event.target.closest('[data-edit-item]');
        const field = event.target.dataset.itemField;
        if (!row || !['package', 'flavor'].includes(field)) return;
        editItems[Number(row.dataset.editItem)][field] = event.target.value;
        updateEditTotal();
    });
    editItemsContainer.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-edit-item]');
        if (!button || editItems.length <= 1) return;
        editItems.splice(Number(button.dataset.removeEditItem), 1);
        renderEditItems();
    });

    function updateEditDeliveryField() {
        const isDelivery = editFulfillmentSelect.value === 'Delivery';
        editDeliveryLocation.hidden = !isDelivery;
        editDeliveryLocation.querySelector('input').required = isDelivery;
    }

    document.querySelector('#edit-order').addEventListener('click', () => {
        const order = orders.find((item) => item.id === selectedOrderId);
        if (!order) return;

        const phoneDigits = String(order.phone).replace(/\D/g, '').replace(/^63/, '').replace(/^0/, '');
        elements.editForm.elements.customer.value = order.customer;
        elements.editForm.elements.phone.value = phoneDigits;
        elements.editForm.elements.email.value = order.email ?? '';
        elements.editForm.elements.schedule.value = order.schedule;
        elements.editForm.elements.fulfillment.value = order.fulfillment_method;
        elements.editForm.elements.delivery_location.value = order.delivery_location ?? '';
        elements.editForm.elements.payment_method.value = order.payment_method;
        elements.editForm.elements.note.value = order.note_raw ?? '';
        editItems = order.items.map((item) => ({ package: item.package_key, flavor: item.flavor }));
        renderEditItems();
        updateEditDeliveryField();
        elements.editDialog.showModal();
    });
    document.querySelector('#close-edit-order-dialog').addEventListener('click', () => elements.editDialog.close());
    document.querySelector('#cancel-edit-order').addEventListener('click', () => elements.editDialog.close());
    elements.editDialog.addEventListener('click', (event) => {
        if (event.target === elements.editDialog) elements.editDialog.close();
    });
    editFulfillmentSelect.addEventListener('change', updateEditDeliveryField);

    elements.editForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const order = orders.find((item) => item.id === selectedOrderId);
        if (!order) return;

        const submitButton = elements.editForm.querySelector('[type="submit"]');
        submitButton.disabled = true;

        try {
            const url = window.bellyssimoAdmin.updateUrlTemplate.replace('__ORDER__', order.database_id);
            const data = new FormData(elements.editForm);
            data.set('_method', 'PATCH');
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: data,
            });
            const payload = await response.json();
            if (!response.ok) {
                const firstError = Object.values(payload.errors ?? {})[0]?.[0];
                throw new Error(firstError ?? payload.message ?? 'Unable to update this order right now.');
            }

            orders = orders.map((item) => item.database_id === payload.order.database_id ? payload.order : item);
            elements.editDialog.close();
            reportsLoaded = false;
            await fetchOrders(ordersPagination.current_page);
            customersLoaded = false;
            showToast(payload.message);
            await refreshSummary();
        } catch (error) {
            showToast(error.message);
        } finally {
            submitButton.disabled = false;
        }
    });
    elements.paymentForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const order = orders.find((item) => item.id === selectedOrderId);
        if (!order) return;
        const button = elements.paymentForm.querySelector('[type="submit"]');
        button.disabled = true;

        try {
            const url = window.bellyssimoAdmin.paymentUrlTemplate.replace('__ORDER__', order.database_id);
            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ amount_paid: Number(document.querySelector('#amount-paid-input').value) }),
            });
            const payload = await response.json();
            if (!response.ok) {
                const firstError = Object.values(payload.errors ?? {})[0]?.[0];
                throw new Error(firstError ?? payload.message ?? 'Unable to save this payment right now.');
            }

            orders = orders.map((item) => item.database_id === payload.order.database_id ? payload.order : item);
            await fetchOrders(ordersPagination.current_page);
            customersLoaded = false;
            showToast(payload.message);
            await refreshSummary();
        } catch (error) {
            showToast(error.message);
        } finally {
            button.disabled = orders.find((item) => item.id === selectedOrderId)?.status === 'Completed';
        }
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
            await fetchOrders(ordersPagination.current_page);
            customersLoaded = false;
            showToast(payload.message);
            await refreshSummary();
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
            const data = new FormData();
            data.set('_method', 'DELETE');
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: data,
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message ?? 'Unable to delete this order right now.');

            selectedOrderId = null;
            elements.deleteDialog.close();
            elements.inspector.classList.remove('open');
            reportsLoaded = false;
            customersLoaded = false;
            await fetchOrders(ordersPagination.current_page);
            await refreshSummary();
            showToast(payload.message);
        } catch (error) {
            showToast(error.message);
        } finally {
            button.disabled = false;
        }
    });

    elements.customersBody.addEventListener('click', (event) => {
        const button = event.target.closest('[data-customer-delete]');
        if (!button) return;
        selectedCustomerId = button.dataset.customerDelete;
        document.querySelector('#delete-customer-name').textContent = button.dataset.customerName;
        elements.deleteCustomerDialog.showModal();
    });
    elements.customerSearch.addEventListener('input', () => {
        ++customersRequestId;
        window.clearTimeout(customerSearchTimer);
        customerSearchTimer = window.setTimeout(() => fetchCustomers(1), 250);
    });
    elements.customersPrev.addEventListener('click', () => fetchCustomers(customersPagination.current_page - 1));
    elements.customersNext.addEventListener('click', () => fetchCustomers(customersPagination.current_page + 1));
    document.querySelector('#close-delete-customer-dialog').addEventListener('click', () => elements.deleteCustomerDialog.close());
    document.querySelector('#cancel-delete-customer').addEventListener('click', () => elements.deleteCustomerDialog.close());
    elements.deleteCustomerDialog.addEventListener('click', (event) => {
        if (event.target === elements.deleteCustomerDialog) elements.deleteCustomerDialog.close();
    });
    document.querySelector('#confirm-delete-customer').addEventListener('click', async () => {
        if (!selectedCustomerId) return;
        const button = document.querySelector('#confirm-delete-customer');
        button.disabled = true;

        try {
            const url = window.bellyssimoAdmin.customerDeleteUrlTemplate.replace('__CUSTOMER__', selectedCustomerId);
            const response = await fetch(url, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message ?? 'Unable to delete these customer details right now.');

            elements.deleteCustomerDialog.close();
            selectedCustomerId = null;
            await fetchCustomers(customersPagination.current_page);
            await refreshSummary();
            showToast(payload.message);
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

            selectedOrderId = payload.order.id;
            elements.search.value = '';
            elements.dateFilter.value = 'all';
            activeStatus = 'all';
            elements.tabs.querySelectorAll('button').forEach((button) => button.classList.toggle('active', button.dataset.status === 'all'));
            elements.form.reset();
            deliveryLocation.hidden = true;
            deliveryLocation.querySelector('input').required = false;
            elements.dialog.close();
            reportsLoaded = false;
            window.location.hash = 'orders';
            await fetchOrders(1);
            await refreshSummary();
            customersLoaded = false;
            if (window.matchMedia('(max-width: 1180px)').matches) elements.inspector.classList.add('open');
            showToast(payload.message);
        } catch (error) {
            showToast(error.message);
        } finally {
            submitButton.disabled = false;
        }
    });

    document.querySelector('#export-button').addEventListener('click', () => {
        const url = new URL(config.ordersExportUrl, window.location.origin);
        const query = elements.search.value.trim();
        if (query) url.searchParams.set('q', query);
        if (activeStatus !== 'all') url.searchParams.set('status', activeStatus);
        if (elements.dateFilter.value !== 'all') url.searchParams.set('date', elements.dateFilter.value);
        window.location.assign(url);
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
        if (section === 'customers' && !customersLoaded) fetchCustomers(1);
        if (section === 'reports') loadReports();
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
    elements.inventoryBody.addEventListener('click', (event) => {
        const button = event.target.closest('[data-inventory-edit]');
        if (!button) return;
        const item = inventoryItems.find((entry) => String(entry.id) === button.dataset.inventoryEdit);
        if (item) openInventoryDialog(item);
    });
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
            if (!data.get('unit_cost')) data.set('unit_cost', '');
            if (id) data.set('_method', 'PATCH');
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: data,
            });
            const payload = await response.json();
            if (!response.ok) {
                const firstError = Object.values(payload.errors ?? {})[0]?.[0];
                throw new Error(firstError ?? 'Unable to save this inventory item.');
            }
            const itemIndex = inventoryItems.findIndex((item) => String(item.id) === String(payload.item.id));
            if (itemIndex >= 0) inventoryItems[itemIndex] = payload.item;
            else inventoryItems.push(payload.item);
            inventoryItems.sort((left, right) => left.name.localeCompare(right.name));
            elements.inventoryDialog.close();
            renderInventory();
            await refreshSummary();
            window.location.hash = 'inventory';
            showToast(payload.message);
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

    updateSummaryDisplay();
    renderInventory();
    showSection();
    render();
});
