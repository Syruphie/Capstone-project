export function renderOrdersReport(data) {
    const s = data.statistics;
    let html = '<p><strong>Period:</strong> ' + (data.from || '') + ' to ' + (data.to || '') + '</p>';
    html += '<p><strong>Total orders:</strong> ' + (s.total || 0) + '</p>';
    if (s.by_status && s.by_status.length) {
        html += '<table class="admin-table"><thead><tr><th>Status</th><th>Count</th></tr></thead><tbody>';
        s.by_status.forEach(function (r) {
            html += '<tr><td>' + r.status + '</td><td>' + r.cnt + '</td></tr>';
        });
        html += '</tbody></table>';
    }
    if (data.rows && data.rows.length) {
        html += '<h4>Orders</h4><table class="admin-table"><thead><tr><th>Order #</th><th>Customer</th><th>Status</th><th>Priority</th><th>Total</th><th>Created</th></tr></thead><tbody>';
        data.rows.forEach(function (r) {
            html +=
                '<tr><td>' +
                (r.order_number || '') +
                '</td><td>' +
                (r.customer_name || '') +
                '</td><td>' +
                (r.status || '') +
                '</td><td>' +
                (r.priority || '') +
                '</td><td>' +
                (r.total_cost || '') +
                '</td><td>' +
                (r.created_at || '') +
                '</td></tr>';
        });
        html += '</tbody></table>';
    }
    return html;
}
