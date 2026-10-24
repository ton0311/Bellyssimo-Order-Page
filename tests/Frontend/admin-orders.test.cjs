const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

// Exercise the real dashboard script with controlled DOM and network boundaries.
function dashboardHarness() {
    const nodes = new Map();
    function element() {
        return {
            value: '', textContent: '', innerHTML: '', hidden: false, disabled: false,
            dataset: {}, content: 'csrf', listeners: {},
            classList: { add() {}, remove() {}, toggle() {} },
            addEventListener(event, handler) { this.listeners[event] = handler; },
            querySelector() { return element(); }, querySelectorAll() { return []; },
            setAttribute() {}, removeAttribute() {}, showModal() {}, close() {}, reset() {},
        };
    }
    const get = (selector) => {
        if (!nodes.has(selector)) nodes.set(selector, element());
        return nodes.get(selector);
    };
    get('#date-filter').value = 'all';
    const config = {
        orders: [], inventoryItems: [], dashboard: { monthly: [], status_counts: {} },
        ordersPagination: { current_page: 1, last_page: 1, total: 0 },
        ordersDataUrl: 'https://example.test/orders',
        customersDataUrl: 'https://example.test/customers',
        summaryUrl: 'https://example.test/summary', statusUrlTemplate: 'https://example.test/orders/__ORDER__/status',
        inventoryUpdateUrlTemplate: 'https://example.test/inventory/__ITEM__',
    };
    const requests = [];
    const context = {
        Intl, Date, URL, structuredClone, Number, String, Object,
        FormData: class extends FormData {
            constructor(form) {
                super();
                for (const [key, value] of Object.entries(form?.formValues ?? {})) this.set(key, value);
            }
        },
        window: {
            bellyssimoAdmin: config, location: { origin: 'https://example.test', hash: '' },
            addEventListener() {}, clearTimeout() {}, setTimeout() {},
            matchMedia() { return { matches: false }; },
        },
        document: {
            addEventListener(event, callback) { if (event === 'DOMContentLoaded') this.ready = callback; },
            querySelector: get, querySelectorAll() { return []; },
            createElement() {
                return { textContent: '', get innerHTML() {
                    return String(this.textContent).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;');
                }};
            },
        },
        fetch(url, options) {
            return new Promise((resolve) => requests.push({
                url: String(url), options,
                respond(payload) { resolve({ ok: true, json: async () => payload }); },
            }));
        },
    };
    let source = fs.readFileSync(path.join(__dirname, '../../public/js/admin-orders.js'), 'utf8');
    const startup = /    updateSummaryDisplay\(\);\s+renderInventory\(\);\s+showSection\(\);\s+render\(\);/;
    assert.match(source, startup);
    source = source.replace(startup, 'globalThis.api = { fetchOrders, fetchCustomers, setOrders: (items, status) => { orders = items; selectedOrderId = items[0]?.id; activeStatus = status; }, escapeHtml, renderCustomers, renderInventory, updateSummaryDisplay, setInventory: (items) => { inventoryItems = items; }, elements, state: () => ({ orders, ordersPagination, customersPagination }), setDashboard: (value) => { dashboard = value; } };');
    vm.runInNewContext(source, context);
    context.document.ready();
    return { api: context.api, requests, get };
}
const pagination = (page = 1, last = 1, total = 0) => ({
    current_page: page, last_page: last, total, per_page: 50,
});
const settle = () => new Promise((resolve) => setImmediate(resolve));

test('older order searches cannot overwrite the newest results', async () => {
    const { api, requests } = dashboardHarness();
    const older = api.fetchOrders();
    const newer = api.fetchOrders();
    requests[1].respond({ orders: [], pagination: pagination(1, 1, 2) });
    await newer;
    requests[0].respond({ orders: [], pagination: pagination(1, 9, 400) });
    await older;
    assert.equal(api.state().ordersPagination.total, 2);
});

test('deleting the final order on the last page returns to the remaining page', async () => {
    const { api, requests } = dashboardHarness();
    const pending = api.fetchOrders(2);
    requests[0].respond({ orders: [], pagination: pagination(2, 1, 50) });
    await settle();
    assert.equal(new URL(requests[1].url).searchParams.get('page'), '1');
    requests[1].respond({ orders: [], pagination: pagination(1, 1, 50) });
    await pending;
    assert.equal(api.state().ordersPagination.current_page, 1);
});

test('customer searches ignore stale responses', async () => {
    const { api, requests } = dashboardHarness();
    const older = api.fetchCustomers();
    const newer = api.fetchCustomers();
    requests[1].respond({ customers: [], pagination: pagination(1, 1, 1) });
    await newer;
    requests[0].respond({ customers: [], pagination: pagination(1, 5, 250) });
    await older;
    assert.equal(api.state().customersPagination.total, 1);
});

test('customer deletion also recovers from an empty last page', async () => {
    const { api, requests } = dashboardHarness();
    const pending = api.fetchCustomers(2);
    requests[0].respond({ customers: [], pagination: pagination(2, 1, 50) });
    await settle();
    assert.equal(new URL(requests[1].url).searchParams.get('page'), '1');
    requests[1].respond({ customers: [], pagination: pagination(1, 1, 50) });
    await pending;
    assert.equal(api.state().customersPagination.current_page, 1);
});

test('customer names cannot escape quoted HTML attributes', () => {
    const { api, get } = dashboardHarness();
    api.renderCustomers([{
        database_id: 1, name: 'Ana" onclick="alert(1)', phone: '+639171234567',
        email: '', orders: 1, completed_orders: 0, lifetime_value: 0, last_order: 'Oct 24, 2026',
    }]);
    assert.match(get('#customers-body').innerHTML, /data-customer-name="Ana&quot; onclick=&quot;alert\(1\)"/);
    assert.doesNotMatch(get('#customers-body').innerHTML, /data-customer-name="Ana" onclick=/);
    assert.equal(api.escapeHtml('<script>'), '&lt;script&gt;');
});

test('chart labels retain decimal thousands and summaries update across sections', () => {
    const { api, get } = dashboardHarness();
    api.setDashboard({
        monthly: [{ label: 'Oct', revenue: 2950, height: 100, orders: 2 }],
        status_counts: {}, total_revenue: 2950, month_revenue: 2950,
        pending: 1, confirmed: 1, declined: 0, cancelled: 0, completed: 0,
    });
    api.updateSummaryDisplay();
    assert.match(get('#overview-monthly-chart').innerHTML, /3\.0k/);
    assert.equal(get('#report-total-revenue').textContent, get('#metric-sales').textContent);
});

test('inventory rendering preserves centavos in unit costs', () => {
    const { api, get } = dashboardHarness();
    api.setInventory([{ id: 1, name: 'Packaging', category: 'Supplies', unit: 'piece', quantity: 10, low_stock_threshold: 2, unit_cost: 125.50 }]);
    api.renderInventory();
    assert.match(get('#inventory-body').innerHTML, /125\.50/);
});

test('typing invalidates a pending search before its debounce timer fires', async () => {
    const { api, requests, get } = dashboardHarness();
    const pending = api.fetchOrders();
    get('#order-search').listeners.input();
    requests[0].respond({ orders: [], pagination: pagination(1, 9, 400) });
    await pending;
    assert.equal(api.state().ordersPagination.total, 0);
});
test('changing status refreshes the active filter and removes a nonmatching order', async () => {
    const { api, requests, get } = dashboardHarness();
    api.setOrders([{ id: 'TB-1', database_id: 1 }], 'Pending confirmation');
    get('#status-select').value = 'Confirmed';
    const pending = get('#save-status').listeners.click();
    requests[0].respond({ order: { id: 'TB-1', database_id: 1, status: 'Confirmed' }, message: 'Saved' });
    await settle();
    assert.equal(new URL(requests[1].url).searchParams.get('status'), 'Pending confirmation');
    requests[1].respond({ orders: [], pagination: pagination() });
    await settle();
    requests[2].respond({ dashboard: { monthly: [], status_counts: {} }, customer_count: 1, inventory_count: 0 });
    await pending;
    assert.equal(api.state().orders.length, 0);
});

test('inventory edits send multipart POST with method override and an empty cost', async () => {
    const { requests, get } = dashboardHarness();
    get('#inventory-form').formValues = { id: '3', name: 'Packaging', unit_cost: '' };
    const pending = get('#inventory-form').listeners.submit({ preventDefault() {} });
    assert.equal(requests[0].options.method, 'POST');
    assert.equal(requests[0].options.body.get('_method'), 'PATCH');
    assert.equal(requests[0].options.body.get('unit_cost'), '');
    requests[0].respond({ item: { id: 3, name: 'Packaging', unit: 'piece', unit_cost: null }, message: 'Saved' });
    await settle();
    requests[1].respond({ dashboard: { monthly: [], status_counts: {} }, customer_count: 0, inventory_count: 1 });
    await pending;
});
