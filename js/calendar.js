(function () {
    'use strict';

    const PENDING = ['submitted', 'pending_approval', 'approved', 'payment_pending', 'payment_confirmed', 'in_queue'];
    const IN_PROGRESS = ['preparation_in_progress', 'testing_in_progress'];
    const COMPLETED = ['results_available', 'completed'];

    function statusGroup(s) {
        if (PENDING.includes(s)) return 'pending';
        if (IN_PROGRESS.includes(s)) return 'inprogress';
        if (COMPLETED.includes(s)) return 'completed';
        return 'pending';
    }

    const apiBase = 'api';
    const pollInterval = 15000;
    let pollTimer = null;
    let currentData = null;

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

    function setStatus(text) {
        if (statusEl) statusEl.textContent = text;
    }

    function fetchCalendar() {
        return fetch(apiBase + '/calendar-data.php')
            .then(function (r) {
                if (!r.ok) throw new Error('Fetch failed');
                return r.json();
            })
            .then(function (j) {
                if (!j.success) throw new Error(j.error || 'API error');
                return j.data;
            });
    }

    function renderQueue(data) {
        const q = data.queue || [];
        queueEmptyEl.style.display = q.length ? 'none' : 'block';
        if (!q.length) {
            queueListEl.querySelectorAll('.queue-subsection').forEach(function (el) { el.remove(); });
            return;
        }

        const byType = { priority: [], standard: [] };
        q.forEach(function (r) {
            const t = r.queue_type === 'priority' ? 'priority' : 'standard';
            byType[t].push(r);
        });

        queueListEl.querySelectorAll('.queue-subsection').forEach(function (el) { el.remove(); });

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
            attachDragDrop(list);
        });
        queueListEl.querySelectorAll('.btn-edit').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const item = btn.closest('.queue-item');
                if (!item) return;
                const qid = item.getAttribute('data-queue-id');
                const entry = (data.queue || []).find(function (r) { return String(r.queue_id) === qid; });
                if (!entry) return;
                openEditModal(entry);
            });
        });
        queueListEl.querySelectorAll('.btn-finish').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const item = btn.closest('.queue-item');
                if (!item) return;
                const qid = item.getAttribute('data-queue-id');
                const entry = (data.queue || []).find(function (r) { return String(r.queue_id) === qid; });
                if (!entry) return;
                openEditModal(entry);
            });
        });
    }

    function escapeHtml(s) {
        if (s == null) return '';
        const div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }

    function attachDragDrop(listEl) {
        const items = listEl.querySelectorAll('.queue-item');
        items.forEach(function (item) {
            item.addEventListener('dragstart', onDragStart);
            item.addEventListener('dragend', onDragEnd);
            item.addEventListener('dragover', onDragOver);
            item.addEventListener('dragleave', onDragLeave);
            item.addEventListener('drop', onDrop);
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
        document.querySelectorAll('.queue-item.drag-over').forEach(function (n) { n.classList.remove('drag-over'); });
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

    function onDrop(e) {
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

        fetch(apiBase + '/calendar-reorder.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ queue_id: parseInt(queueId, 10), new_position: newPos })
        })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (j.success) load(); else setStatus('Reorder failed: ' + (j.error || 'Unknown'));
            })
            .catch(function () { setStatus('Reorder failed'); });
    }

    function renderUtilization(data) {
        const util = data.utilization || [];
        utilEmptyEl.style.display = util.length ? 'none' : 'block';
        if (!util.length) {
            utilGridEl.querySelectorAll('.util-card').forEach(function (el) { el.remove(); });
            return;
        }

        utilGridEl.querySelectorAll('.util-card').forEach(function (c) { c.remove(); });
        util.forEach(function (u) {
            let cardHtml = '<div class="util-card"><h3>' + escapeHtml(u.name) + '</h3><div class="util-slots">';
            const slots = u.slots || [];
            if (!slots.length) {
                cardHtml += '<div class="util-slot util-slot-empty">No bookings</div>';
            } else {
                slots.forEach(function (s) {
                    const sg = statusGroup(s.order_status);
                    const start = s.scheduled_start ? new Date(s.scheduled_start).toLocaleString(undefined, { dateStyle: 'short', timeStyle: 'short' }) : '';
                    const end = s.scheduled_end ? new Date(s.scheduled_end).toLocaleString(undefined, { timeStyle: 'short' }) : '';
                    cardHtml += '<div class="util-slot util-slot-' + sg + '">';
                    cardHtml += '<span class="util-slot-order">' + escapeHtml(s.order_number) + '</span>';
                    cardHtml += '<span class="util-slot-time">' + escapeHtml(start) + ' – ' + escapeHtml(end) + '</span>';
                    cardHtml += '</div>';
                });
            }
            cardHtml += '</div></div>';
            const wrap = document.createElement('div');
            wrap.innerHTML = cardHtml;
            utilGridEl.appendChild(wrap.firstElementChild);
        });
    }

    function toDateTimeInput(val) {
        if (!val) return '';
        return val.replace(' ', 'T').slice(0, 16);
    }

    function openEditModal(entry) {
        if (!entry || !editModal || !editForm) return;
        editQueueId.value = entry.queue_id;
        editStart.value = toDateTimeInput(entry.scheduled_start);
        editEnd.value = toDateTimeInput(entry.scheduled_end);
        if (editMessage) editMessage.value = '';
        if (finishMessageInEdit) finishMessageInEdit.value = '';
        if (finishAttachmentInEdit) finishAttachmentInEdit.value = '';
        editModal.setAttribute('aria-hidden', 'false');
    }

    function closeEditModal() {
        if (editModal) editModal.setAttribute('aria-hidden', 'true');
    }

    function load() {
        setStatus('Loading…');
        fetchCalendar()
            .then(function (data) {
                currentData = data;
                renderQueue(data);
                renderUtilization(data);
                setStatus('Updated ' + new Date().toLocaleTimeString());
            })
            .catch(function (e) {
                setStatus('Error: ' + (e.message || 'Could not load data'));
            });
    }

    function toDateTimeLocal(val) {
        if (!val) return '';
        return val.replace('T', ' ') + ':00';
    }

    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const qid = parseInt(editQueueId.value, 10);
            const start = toDateTimeLocal(editStart.value);
            const end = toDateTimeLocal(editEnd.value);
            const message = editMessage ? editMessage.value.trim() : '';
            if (!qid || !start || !end) return;

            fetch(apiBase + '/calendar-reschedule.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ queue_id: qid, scheduled_start: start, scheduled_end: end, message: message })
            })
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (j.success) {
                        closeEditModal();
                        load();
                    } else {
                        setStatus('Reschedule failed: ' + (j.error || 'Unknown'));
                    }
                })
                .catch(function () { setStatus('Reschedule failed'); });
        });
    }

    function finishOrder() {
        if (!editQueueId || !editQueueId.value) {
            setStatus('Cannot finish: no order selected.');
            return;
        }

        const formData = new FormData();
        formData.append('queue_id', String(editQueueId.value));

        if (finishMessageInEdit && finishMessageInEdit.value.trim()) {
            formData.append('message', finishMessageInEdit.value.trim());
        }

        if (finishAttachmentInEdit && finishAttachmentInEdit.files && finishAttachmentInEdit.files[0]) {
            formData.append('attachment', finishAttachmentInEdit.files[0]);
        }

        if (btnFinishOrderInEdit) btnFinishOrderInEdit.disabled = true;
        setStatus('Finishing order…');

        fetch(apiBase + '/order-complete.php', { method: 'POST', body: formData })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (btnFinishOrderInEdit) btnFinishOrderInEdit.disabled = false;
                if (j.success) {
                    closeEditModal();
                    load();
                    setStatus('Order finished and moved to history.');
                } else {
                    setStatus('Finish order failed: ' + (j.error || 'Unknown'));
                }
            })
            .catch(function (err) {
                if (btnFinishOrderInEdit) btnFinishOrderInEdit.disabled = false;
                setStatus('Finish order failed.');
                console.error('Finish order error', err);
            });
    }

    if (btnFinishOrderInEdit) {
        btnFinishOrderInEdit.addEventListener('click', function (e) {
            e.preventDefault();
            finishOrder();
        });
    }

    if (btnCancelEdit) btnCancelEdit.addEventListener('click', closeEditModal);
    if (editModal) {
        editModal.addEventListener('click', function (e) {
            if (e.target === editModal) closeEditModal();
        });
    }

    if (refreshBtn) refreshBtn.addEventListener('click', function () { load(); });

    load();
    pollTimer = setInterval(load, pollInterval);
})();
