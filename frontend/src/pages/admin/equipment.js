import { initPageBootstrap } from '../../utils/pageBootstrap.js';
import { postEquipmentAdd } from '../../services/api/equipmentApi.js';

document.addEventListener('DOMContentLoaded', function () {
    initPageBootstrap();

    var addBtn = document.querySelector('.add-equipment-btn');
    var modal = document.getElementById('addEquipmentModal');
    var form = document.getElementById('addEquipmentForm');
    var cancelBtn = document.getElementById('addEquipmentCancel');
    if (!addBtn || !modal || !form) return;
    function openModal() {
        modal.setAttribute('aria-hidden', 'false');
    }
    function closeModal() {
        modal.setAttribute('aria-hidden', 'true');
    }
    addBtn.addEventListener('click', openModal);
    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var fd = new FormData(form);
        var payload = {
            name: fd.get('name') || '',
            equipment_type: fd.get('equipment_type') || '',
            processing_time_per_sample: parseInt(fd.get('processing_time_per_sample'), 10) || 0,
            warmup_time: parseInt(fd.get('warmup_time'), 10) || 0,
            break_interval: parseInt(fd.get('break_interval'), 10) || 0,
            break_duration: parseInt(fd.get('break_duration'), 10) || 0,
            daily_capacity: parseInt(fd.get('daily_capacity'), 10) || 0,
            is_available: fd.get('is_available') === 'on',
            last_maintenance: fd.get('last_maintenance') || null,
        };
        var submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        postEquipmentAdd(payload)
            .then(function (res) {
                if (res.success) {
                    closeModal();
                    window.location.reload();
                } else {
                    alert(res.error || 'Failed to add equipment');
                }
            })
            .catch(function () {
                alert('Request failed');
            })
            .then(function () {
                submitBtn.disabled = false;
            });
    });
});
