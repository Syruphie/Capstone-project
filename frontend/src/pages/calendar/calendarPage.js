import { fetchCalendarData } from '../../services/api/calendarApi.js';
import { renderQueue } from '../../components/calendar/queueView.js';
import { renderUtilization } from '../../components/calendar/utilizationView.js';
import { bindEditModal } from '../../components/calendar/editModal.js';
import { openOrderDetailsDialog } from '../../utils/orderDetailsDialog.js';

const POLL_INTERVAL_MS = 15000;

(function initCalendarPage() {
    const statusEl = document.getElementById('calendarStatus');
    const refreshBtn = document.getElementById('btnRefresh');
    const queueListEl = document.getElementById('queueList');
    const queueEmptyEl = document.getElementById('queueEmpty');
    const utilGridEl = document.getElementById('utilizationGrid');
    const utilEmptyEl = document.getElementById('utilEmpty');
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editForm');
    const editQueueId = document.getElementById('editQueueId');
    const editStart = document.getElementById('editStart');
    const editEnd = document.getElementById('editEnd');
    const btnCancelEdit = document.getElementById('btnCancelEdit');
    const editMessage = document.getElementById('editMessage');
    const finishMessageInEdit = document.getElementById('finishMessageInEdit');
    const finishAttachmentInEdit = document.getElementById('finishAttachmentInEdit');
    const btnFinishOrderInEdit = document.getElementById('btnFinishOrderInEdit');

    if (!queueListEl || !queueEmptyEl || !utilGridEl || !utilEmptyEl) return;

    function setStatus(text) {
        if (statusEl) statusEl.textContent = text;
    }

    let openEditModal = function () {};
    let openDetailsModal = function (entry) {
        openOrderDetailsDialog({
            orderNumber: entry.order_number,
            status: entry.order_status ? entry.order_status.replaceAll('_', ' ') : '',
            priority: entry.priority,
            customerName: entry.customer_name,
            companyName: entry.company_name,
            sampleTypes: entry.sample_types || [],
            createdAt: entry.created_at,
            estimatedCompletion: entry.estimated_completion,
            orderNote: entry.order_note
        });
    };

    function load() {
        setStatus('Loading…');
        fetchCalendarData()
            .then(function (data) {
                renderQueue(queueListEl, queueEmptyEl, data, openEditModal, openDetailsModal, setStatus, load);
                renderUtilization(utilGridEl, utilEmptyEl, data);
                setStatus('Updated ' + new Date().toLocaleTimeString());
            })
            .catch(function (e) {
                setStatus('Error: ' + (e.message || 'Could not load data'));
            });
    }

    const modalApi = bindEditModal(
        editModal,
        editForm,
        editQueueId,
        editStart,
        editEnd,
        btnCancelEdit,
        editMessage,
        finishMessageInEdit,
        finishAttachmentInEdit,
        btnFinishOrderInEdit,
        setStatus,
        load
    );
    openEditModal = modalApi.openEditModal;

    if (refreshBtn) refreshBtn.addEventListener('click', load);

    load();
    setInterval(load, POLL_INTERVAL_MS);
})();
