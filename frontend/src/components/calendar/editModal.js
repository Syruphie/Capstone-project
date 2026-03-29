import { postCalendarReschedule, postOrderComplete } from '../../services/api/calendarApi.js';

function toDateTimeInput(val) {
    if (!val) return '';
    return val.replace(' ', 'T').slice(0, 16);
}

function toDateTimeLocal(val) {
    if (!val) return '';
    return val.replace('T', ' ') + ':00';
}

export function bindEditModal(
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
) {
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

        postOrderComplete(formData)
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

    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const qid = parseInt(editQueueId.value, 10);
            const start = toDateTimeLocal(editStart.value);
            const end = toDateTimeLocal(editEnd.value);
            const message = editMessage ? editMessage.value.trim() : '';
            if (!qid || !start || !end) return;

            postCalendarReschedule({
                queue_id: qid,
                scheduled_start: start,
                scheduled_end: end,
                message: message,
            })
                .then(function (j) {
                    if (j.success) {
                        closeEditModal();
                        load();
                    } else {
                        setStatus('Reschedule failed: ' + (j.error || 'Unknown'));
                    }
                })
                .catch(function () {
                    setStatus('Reschedule failed');
                });
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

    return { openEditModal };
}
