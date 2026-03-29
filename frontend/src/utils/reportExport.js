import { buildCsvFromRows, csvEscapeCell, downloadFile } from './exportHelpers.js';

/**
 * Report-specific export shapes. Uses exportHelpers for generic CSV/download primitives.
 */
export function exportReportToCsv(currentReportData) {
    if (!currentReportData) return;

    const rows = currentReportData.rows || [];

    if (currentReportData.report === 'equipment' && currentReportData.equipment) {
        const lines = ['Name,Type,Available,Last Maintenance,Delay Count'];
        currentReportData.equipment.forEach((eq) => {
            lines.push(
                [
                    eq.name,
                    eq.equipment_type,
                    eq.is_available ? 'Yes' : 'No',
                    eq.last_maintenance || '',
                    eq.delay_count || 0,
                ]
                    .map(csvEscapeCell)
                    .join(',')
            );
        });
        downloadFile(lines.join('\n'), 'equipment-report.csv', 'text/csv');
        return;
    }

    if (!rows.length) {
        downloadFile('No data', currentReportData.report + '-report.csv', 'text/csv');
        return;
    }

    const headers = Object.keys(rows[0]);
    const csv = buildCsvFromRows(headers, rows);
    downloadFile(csv, currentReportData.report + '-report.csv', 'text/csv');
}

export function exportReportToJson(currentReportData) {
    if (!currentReportData) return;
    downloadFile(JSON.stringify(currentReportData, null, 2), currentReportData.report + '-report.json', 'application/json');
}
