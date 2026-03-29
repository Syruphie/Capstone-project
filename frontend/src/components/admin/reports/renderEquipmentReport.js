export function renderEquipmentReport(data) {
    let html = '<p><strong>Equipment utilization, delays, and maintenance</strong></p>';
    if (data.equipment && data.equipment.length) {
        data.equipment.forEach(function (eq) {
            html += '<div class="report-equipment-block"><h4>' + (eq.name || '') + ' (' + (eq.equipment_type || '') + ')</h4>';
            html +=
                '<p>Status: ' +
                (eq.is_available ? 'Available' : 'Unavailable') +
                ' | Last maintenance: ' +
                (eq.last_maintenance || '—') +
                ' | Delay count: ' +
                (eq.delay_count || 0) +
                '</p>';
            if (eq.delays && eq.delays.length) {
                html += '<table class="admin-table"><thead><tr><th>Delay start</th><th>Duration (min)</th><th>Reason</th></tr></thead><tbody>';
                eq.delays.forEach(function (d) {
                    html +=
                        '<tr><td>' +
                        (d.delay_start || '') +
                        '</td><td>' +
                        (d.delay_duration || '') +
                        '</td><td>' +
                        (d.reason || '') +
                        '</td></tr>';
                });
                html += '</tbody></table>';
            }
            html += '</div>';
        });
    } else {
        html += '<p class="empty-state">No equipment data.</p>';
    }
    return html;
}
