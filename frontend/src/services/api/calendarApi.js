import { endpointUrl } from './apiBase.js';

export function fetchCalendarData() {
    return fetch(endpointUrl('calendar-data'))
        .then(function (r) {
            if (!r.ok) throw new Error('Fetch failed');
            return r.json();
        })
        .then(function (j) {
            if (!j.success) throw new Error(j.error || 'API error');
            return j.data;
        });
}

export function postCalendarReorder(queueId, newPosition) {
    return fetch(endpointUrl('calendar-reorder'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ queue_id: queueId, new_position: newPosition }),
    }).then((r) => r.json());
}

export function postCalendarReschedule(payload) {
    return fetch(endpointUrl('calendar-reschedule'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
    }).then((r) => r.json());
}

export function postOrderComplete(formData) {
    return fetch(endpointUrl('order-complete'), {
        method: 'POST',
        body: formData,
    }).then((r) => r.json());
}
