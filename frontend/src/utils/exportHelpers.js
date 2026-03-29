/**
 * Generic download and CSV helpers for reuse across reports and future exports.
 */

export function downloadFile(content, filename, mime) {
    const a = document.createElement('a');
    a.href = 'data:' + mime + ';charset=utf-8,' + encodeURIComponent(content);
    a.download = filename;
    a.click();
}

export function csvEscapeCell(value) {
    return '"' + String(value ?? '').replace(/"/g, '""') + '"';
}

export function buildCsvFromRows(headers, rows) {
    const lines = [
        headers.map(csvEscapeCell).join(','),
        ...rows.map((row) => headers.map((h) => csvEscapeCell(row[h] ?? '')).join(',')),
    ];
    return lines.join('\n');
}
