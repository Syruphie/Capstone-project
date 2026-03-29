import { endpointUrl } from './apiBase.js';

export function postEquipmentAdd(payload) {
    return fetch(endpointUrl('equipment-add'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
    }).then((r) => r.json());
}
