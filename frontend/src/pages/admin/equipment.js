import { initPageBootstrap } from '../../utils/pageBootstrap.js';
import { postEquipmentAdd } from '../../services/api/equipmentApi.js';

document.addEventListener('DOMContentLoaded', function () {
    initPageBootstrap();

    // ---------- ADD EQUIPMENT ----------
    var addBtn = document.querySelector('.add-equipment-btn');
    var addModal = document.getElementById('addEquipmentModal');
    var addForm = document.getElementById('addEquipmentForm');
    var addCancelBtn = document.getElementById('addEquipmentCancel');

    function openModal(modal) {
        if (modal) modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal(modal) {
        if (modal) modal.setAttribute('aria-hidden', 'true');
    }

    if (addBtn && addModal && addForm) {
        addBtn.addEventListener('click', function () {
            openModal(addModal);
        });

        if (addCancelBtn) {
            addCancelBtn.addEventListener('click', function () {
                closeModal(addModal);
            });
        }

        addModal.addEventListener('click', function (e) {
            if (e.target === addModal) closeModal(addModal);
        });

        addForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var fd = new FormData(addForm);
            var payload = {
                name: fd.get('name') || '',
                equipment_type: fd.get('equipment_type') || '',
                processing_time_per_sample: parseInt(fd.get('processing_time_per_sample'), 10) || 0,
                warmup_time: parseInt(fd.get('warmup_time'), 10) || 0,
                break_interval: parseInt(fd.get('break_interval'), 10) || 0,
                break_duration: parseInt(fd.get('break_duration'), 10) || 0,
                daily_capacity: parseInt(fd.get('daily_capacity'), 10) || 0,
                is_available: fd.get('is_available') === 'on',
                last_maintenance: fd.get('last_maintenance') || null
            };

            var submitBtn = addForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            postEquipmentAdd(payload)
                .then(function (res) {
                    if (res.success) {
                        closeModal(addModal);
                        window.location.reload();
                    } else {
                        alert(res.error || 'Failed to add equipment');
                    }
                })
                .catch(function () {
                    alert('Request failed');
                })
                .then(function () {
                    if (submitBtn) submitBtn.disabled = false;
                });
        });
    }

    // ---------- EDIT EQUIPMENT ----------
    var editModal = document.getElementById('editEquipmentModal');
    var editForm = document.getElementById('editEquipmentForm');
    var editCancelBtn = document.getElementById('editEquipmentCancel');
    var editButtons = document.querySelectorAll('.btn-edit-equipment');

    var currentEditingRow = null;

    function fillEditForm(button) {
        document.getElementById('edit_eq_name').value = button.dataset.name || '';
        document.getElementById('edit_eq_type').value = button.dataset.type || '';
        document.getElementById('edit_eq_processing').value = button.dataset.processing || 0;
        document.getElementById('edit_eq_warmup').value = button.dataset.warmup || 0;
        document.getElementById('edit_eq_break_interval').value = button.dataset.breakInterval || 0;
        document.getElementById('edit_eq_break_duration').value = button.dataset.breakDuration || 0;
        document.getElementById('edit_eq_capacity').value = button.dataset.capacity || 0;
        document.getElementById('edit_eq_available').checked = button.dataset.available === '1';
    }

    if (editModal && editForm && editButtons.length) {
        editButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                currentEditingRow = button.closest('tr');
                fillEditForm(button);
                openModal(editModal);
            });
        });

        if (editCancelBtn) {
            editCancelBtn.addEventListener('click', function () {
                closeModal(editModal);
            });
        }

        editModal.addEventListener('click', function (e) {
            if (e.target === editModal) closeModal(editModal);
        });

        editForm.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!currentEditingRow) return;

            var name = document.getElementById('edit_eq_name').value;
            var type = document.getElementById('edit_eq_type').value;
            var processing = document.getElementById('edit_eq_processing').value;
            var warmup = document.getElementById('edit_eq_warmup').value;
            var breakInterval = document.getElementById('edit_eq_break_interval').value;
            var capacity = document.getElementById('edit_eq_capacity').value;
            var isAvailable = document.getElementById('edit_eq_available').checked;

            currentEditingRow.children[0].textContent = name;
            currentEditingRow.children[1].textContent = type;
            currentEditingRow.children[2].textContent = processing + ' min';
            currentEditingRow.children[3].textContent = warmup + ' min';
            currentEditingRow.children[4].textContent = breakInterval;
            currentEditingRow.children[5].textContent = capacity;

            var statusCell = currentEditingRow.children[6].querySelector('.status-pill');
            if (statusCell) {
                statusCell.textContent = isAvailable ? 'Available' : 'Unavailable';
                statusCell.classList.remove('available', 'unavailable');
                statusCell.classList.add(isAvailable ? 'available' : 'unavailable');
            }

            closeModal(editModal);
        });
    }

    // ---------- DELAY EQUIPMENT ----------
    var delayButtons = document.querySelectorAll('.btn-delay-equipment');

    delayButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var row = button.closest('tr');
            if (!row) return;

            var statusCell = row.querySelector('.status-pill');
            if (!statusCell) return;

            statusCell.textContent = 'Unavailable';
            statusCell.classList.remove('available');
            statusCell.classList.add('unavailable');

            alert('Equipment marked as delayed/unavailable.');
        });
    });
});