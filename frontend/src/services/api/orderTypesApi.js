import { endpointUrl } from './apiBase.js';

const BASE = 'order-types';

export function fetchOrderTypeById(id) {
    return fetch(endpointUrl(BASE, { id: String(id) })).then((r) => r.json());
}

export function createOrderType(payload) {
    return fetch(endpointUrl(BASE), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
    }).then((r) => r.json());
}

export function updateOrderType(payload) {
    return fetch(endpointUrl(BASE), {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
    }).then((r) => r.json());
}

export function deleteOrderType(id) {
    return fetch(endpointUrl(BASE), {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id }),
    }).then((r) => r.json());
}
