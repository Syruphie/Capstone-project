export function renderRevenueReport(data) {
    let html = '<p><strong>Period:</strong> ' + (data.from || '') + ' to ' + (data.to || '') + '</p>';
    html += '<p><strong>Revenue:</strong> $' + parseFloat(data.revenue || 0).toFixed(2) + '</p>';
    html += '<p><strong>Orders (paid/confirmed):</strong> ' + (data.order_count || 0) + '</p>';
    if (data.rows && data.rows.length) {
        html += '<h4>Orders</h4><table class="admin-table"><thead><tr><th>Order #</th><th>Customer</th><th>Status</th><th>Total</th><th>Created</th></tr></thead><tbody>';
        data.rows.forEach(function (r) {
            html +=
                '<tr><td>' +
                (r.order_number || '') +
                '</td><td>' +
                (r.customer_name || '') +
                '</td><td>' +
                (r.status || '') +
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
