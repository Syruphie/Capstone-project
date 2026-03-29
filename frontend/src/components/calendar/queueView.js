import { escapeHtml } from '../../utils/dom.js';
import { postCalendarReorder } from '../../services/api/calendarApi.js';

const PENDING = ['submitted', 'pending_approval', 'approved', 'payment_pending', 'payment_confirmed', 'in_queue'];
const IN_PROGRESS = ['preparation_in_progress', 'testing_in_progress'];
const COMPLETED = ['results_available', 'completed'];

function statusGroup(s) {
    if (PENDING.includes(s)) return 'pending';
    if (IN_PROGRESS.includes(s)) return 'inprogress';
    if (COMPLETED.includes(s)) return 'completed';
    return 'pending';
}

export function renderQueue(queueListEl, queueEmptyEl, data, openEditModal, setStatus, load) {
    const q = data.queue || [];
    queueEmptyEl.style.display = q.length ? 'none' : 'block';
    if (!q.length) {
        queueListEl.querySelectorAll('.queue-subsection').forEach(function (el) {
            el.remove();
        });
        return;
    }

    const byType = { priority: [], standard: [] };
    q.forEach(function (r) {
        const t = r.queue_type === 'priority' ? 'priority' : 'standard';
        byType[t].push(r);
    });

    queueListEl.querySelectorAll('.queue-subsection').forEach(function (el) {
        el.remove();
    });

    ['priority', 'standard'].forEach(function (t) {
        const items = byType[t];
        if (!items.length) return;
        const title = t === 'priority' ? 'Priority queue' : 'Standard queue';
        let html = '<div class="queue-subsection" data-queue-type="' + t + '">';
        html += '<h3>' + title + '</h3>';
        html += '<div class="queue-items" data-queue-type="' + t + '">';
        items.forEach(function (r, i) {
            const sg = statusGroup(r.order_status);
            const types = (r.sample_types || []).join(', ') || '—';
            const eq = r.equipment_name || '—';
            const comp = r.estimated_completion
                ? new Date(r.estimated_completion).toLocaleString(undefined, { dateStyle: 'short', timeStyle: 'short' })
                : '—';
            const pos = i + 1;
            html += '<div class="queue-item queue-status-' + sg + '" draggable="true" data-queue-id="' + r.queue_id + '" data-position="' + pos + '" data-order-number="' + r.order_number + '">';
            html += '<span class="queue-item-handle" aria-hidden="true">⋮⋮</span>';
            html += '<div class="queue-item-main">';
            html += '<div class="queue-item-order">' + escapeHtml(r.order_number) + '</div>';
            html += '<div class="queue-item-meta"><span>Sample: ' + escapeHtml(types) + '</span></div>';
            html += '</div>';
            html += '<div class="queue-item-equipment">' + escapeHtml(eq) + '</div>';
            html += '<div class="queue-item-completion">' + escapeHtml(comp) + '</div>';
            html += '<div class="queue-item-actions">';
            html += '<button type="button" class="btn btn-small btn-secondary btn-edit">Edit</button>';
            html += '<button type="button" class="btn btn-small btn-primary btn-finish">Finish</button>';
            html += '</div>';
            html += '</div>';
        });
        html += '</div></div>';
        const wrap = document.createElement('div');
        wrap.innerHTML = html;
        queueListEl.appendChild(wrap.firstElementChild);
    });

    queueListEl.querySelectorAll('.queue-items').forEach(function (list) {
        attachDragDrop(list, setStatus, load);
    });
    queueListEl.querySelectorAll('.btn-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const item = btn.closest('.queue-item');
            if (!item) return;
            const qid = item.getAttribute('data-queue-id');
            const entry = (data.queue || []).find(function (r) {
                return String(r.queue_id) === qid;
            });
            if (!entry) return;
            openEditModal(entry);
        });
    });
    queueListEl.querySelectorAll('.btn-finish').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const item = btn.closest('.queue-item');
            if (!item) return;
            const qid = item.getAttribute('data-queue-id');
            const entry = (data.queue || []).find(function (r) {
                return String(r.queue_id) === qid;
            });
            if (!entry) return;
            openEditModal(entry);
        });
    });
}

function attachDragDrop(listEl, setStatus, load) {
    const items = listEl.querySelectorAll('.queue-item');
    items.forEach(function (item) {
        item.addEventListener('dragstart', onDragStart);
        item.addEventListener('dragend', onDragEnd);
        item.addEventListener('dragover', onDragOver);
        item.addEventListener('dragleave', onDragLeave);
        item.addEventListener('drop', function (e) {
            onDrop(e, setStatus, load);
        });
    });
}

function onDragStart(e) {
    if (e.target.closest('button')) return;
    const el = e.target.closest('.queue-item');
    if (!el) return;
    el.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', el.getAttribute('data-queue-id'));
    e.dataTransfer.setData('queue-type', el.closest('.queue-items').getAttribute('data-queue-type'));
}

function onDragEnd(e) {
    const el = e.target.closest('.queue-item');
    if (el) el.classList.remove('dragging');
    document.querySelectorAll('.queue-item.drag-over').forEach(function (n) {
        n.classList.remove('drag-over');
    });
}

function onDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    const el = e.target.closest('.queue-item');
    const list = e.target.closest('.queue-items');
    const srcType = e.dataTransfer.getData('queue-type');
    if (el && list && list.getAttribute('data-queue-type') === srcType) {
        el.classList.add('drag-over');
    }
}

function onDragLeave(e) {
    const el = e.target.closest('.queue-item');
    if (el) el.classList.remove('drag-over');
}

function onDrop(e, setStatus, load) {
    e.preventDefault();
    const el = e.target.closest('.queue-item');
    const list = e.target.closest('.queue-items');
    const srcType = e.dataTransfer.getData('queue-type');
    const queueId = e.dataTransfer.getData('text/plain');
    if (!el || !list || list.getAttribute('data-queue-type') !== srcType || !queueId) return;
    el.classList.remove('drag-over');

    const items = Array.from(list.querySelectorAll('.queue-item'));
    const idx = items.indexOf(el);
    if (idx < 0) return;
    const newPos = idx + 1;

    postCalendarReorder(parseInt(queueId, 10), newPos)
        .then(function (j) {
            if (j.success) load();
            else setStatus('Reorder failed: ' + (j.error || 'Unknown'));
        })
        .catch(function () {
            setStatus('Reorder failed');
        });
}
