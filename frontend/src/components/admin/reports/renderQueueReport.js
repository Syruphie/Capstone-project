export function renderQueueReport(data) {
    const s = data.statistics || {};
    let html = '<p><strong>Period:</strong> ' + (data.from || '') + ' to ' + (data.to || '') + '</p>';
    html +=
        '<p><strong>Standard queue length:</strong> ' +
        (s.standard_queue_length || 0) +
        ' | <strong>Priority queue length:</strong> ' +
        (s.priority_queue_length || 0) +
        '</p>';
    html += '<p><strong>Average wait (minutes):</strong> ' + (s.average_wait_minutes || 0) + '</p>';
    if (data.rows && data.rows.length) {
        html += '<h4>Queue entries</h4><table class="admin-table"><thead><tr><th>Order #</th><th>Equipment</th><th>Scheduled start</th><th>Scheduled end</th><th>Type</th></tr></thead><tbody>';
        data.rows.forEach(function (r) {
            html +=
                '<tr><td>' +
                (r.order_number || '') +
                '</td><td>' +
                (r.equipment_name || '') +
                '</td><td>' +
                (r.scheduled_start || '') +
                '</td><td>' +
                (r.scheduled_end || '') +
                '</td><td>' +
                (r.queue_type || '') +
                '</td></tr>';
        });
        html += '</tbody></table>';
    }
    return html;
}
