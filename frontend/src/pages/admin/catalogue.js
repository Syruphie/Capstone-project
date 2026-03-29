import { initPageBootstrap } from '../../utils/pageBootstrap.js';
import { fetchOrderTypeById, deleteOrderType, createOrderType, updateOrderType } from '../../services/api/orderTypesApi.js';

document.addEventListener('DOMContentLoaded', function () {
    initPageBootstrap();

    var addBtn = document.getElementById('addOrderTypeBtn');
    var modal = document.getElementById('orderTypeModal');
    var form = document.getElementById('orderTypeForm');
    var titleEl = document.getElementById('orderTypeModalTitle');
    var cancelBtn = document.getElementById('orderTypeModalCancel');
    var tbody = document.getElementById('orderTypesTableBody');
    if (!addBtn || !modal || !form || !tbody || !titleEl || !cancelBtn) return;

    function openModal(editRow) {
        document.getElementById('ot_id').value = editRow ? editRow.id : '';
        document.getElementById('ot_name').value = editRow ? editRow.name : '';
        document.getElementById('ot_description').value = editRow ? editRow.description || '' : '';
        var st = document.getElementById('ot_sample_type');
        if (st) st.value = editRow && (editRow.sample_type === 'liquid' || editRow.sample_type === 'ore') ? editRow.sample_type : 'ore';
        document.getElementById('ot_cost').value = editRow ? editRow.cost : '0';
        document.getElementById('ot_active').checked = editRow ? !!editRow.is_active : true;
        document.getElementById('ot_activeWrap').style.display = editRow ? 'block' : 'none';
        titleEl.textContent = editRow ? 'Edit Order Type' : 'Add Order Type';
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        modal.setAttribute('aria-hidden', 'true');
    }

    addBtn.addEventListener('click', function () {
        openModal(null);
    });
    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });
    tbody.addEventListener('click', function (e) {
        var editBtn = e.target.closest('.btn-edit-type');
        var delBtn = e.target.closest('.btn-delete-type');
        if (editBtn) {
            var id = parseInt(editBtn.getAttribute('data-id'), 10);
            fetchOrderTypeById(id).then(function (res) {
                if (res.success && res.data) openModal(res.data);
            });
        }
        if (delBtn) {
            if (!confirm('Delete this order type?')) return;
            var delId = parseInt(delBtn.getAttribute('data-id'), 10);
            deleteOrderType(delId)
                .then(function (res) {
                    if (res.success) window.location.reload();
                    else alert(res.error);
                });
        }
    });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var id = document.getElementById('ot_id').value;
        var payload = {
            name: document.getElementById('ot_name').value.trim(),
            description: document.getElementById('ot_description').value.trim(),
            sample_type: (document.getElementById('ot_sample_type') || {}).value || 'ore',
            cost: parseFloat(document.getElementById('ot_cost').value) || 0,
            is_active: document.getElementById('ot_active').checked,
        };
        if (id) {
            payload.id = parseInt(id, 10);
            updateOrderType(payload)
                .then(function (res) {
                    if (res.success) window.location.reload();
                    else alert(res.error || 'Failed');
                })
                .catch(function () {
                    alert('Request failed');
                });
        } else {
            createOrderType(payload)
                .then(function (res) {
                    if (res.success) window.location.reload();
                    else alert(res.error || 'Failed');
                })
                .catch(function () {
                    alert('Request failed');
                });
        }
    });
});
