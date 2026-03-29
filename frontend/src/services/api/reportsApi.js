import { endpointUrl } from './apiBase.js';

export function fetchReport(type, range, fromVal, toVal) {
    const query = { type, range };
    if (range === 'custom') {
        query.from = fromVal || '';
        query.to = toVal || '';
    }
    return fetch(endpointUrl('reports', query)).then((r) => r.json());
}
