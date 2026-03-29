import { escapeHtml } from '../../utils/dom.js';

const PENDING = ['submitted', 'pending_approval', 'approved', 'payment_pending', 'payment_confirmed', 'in_queue'];
const IN_PROGRESS = ['preparation_in_progress', 'testing_in_progress'];
const COMPLETED = ['results_available', 'completed'];

function statusGroup(s) {
    if (PENDING.includes(s)) return 'pending';
    if (IN_PROGRESS.includes(s)) return 'inprogress';
    if (COMPLETED.includes(s)) return 'completed';
    return 'pending';
}

export function renderUtilization(utilGridEl, utilEmptyEl, data) {
    const util = data.utilization || [];
    utilEmptyEl.style.display = util.length ? 'none' : 'block';
    if (!util.length) {
        utilGridEl.querySelectorAll('.util-card').forEach(function (el) {
            el.remove();
        });
        return;
    }

    utilGridEl.querySelectorAll('.util-card').forEach(function (c) {
        c.remove();
    });
    util.forEach(function (u) {
        let cardHtml = '<div class="util-card"><h3>' + escapeHtml(u.name) + '</h3><div class="util-slots">';
        const slots = u.slots || [];
        if (!slots.length) {
            cardHtml += '<div class="util-slot util-slot-empty">No bookings</div>';
        } else {
            slots.forEach(function (s) {
                const sg = statusGroup(s.order_status);
                const start = s.scheduled_start
                    ? new Date(s.scheduled_start).toLocaleString(undefined, { dateStyle: 'short', timeStyle: 'short' })
                    : '';
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
