export const API_PHP = 'api.php';

/**
 * Build `api.php?endpoint=...&...` query URLs.
 */
export function endpointUrl(name, query = {}) {
    const p = new URLSearchParams();
    p.set('endpoint', name);
    for (const [k, v] of Object.entries(query)) {
        if (v !== undefined && v !== null && v !== '') {
            p.set(k, String(v));
        }
    }
    return `${API_PHP}?${p.toString()}`;
}
