import { attachRequiredFieldValidation } from '../../utils/formValidation.js';
import { initAutoHideAlerts } from '../../utils/alerts.js';

document.addEventListener('DOMContentLoaded', function () {
    attachRequiredFieldValidation();
    initAutoHideAlerts();

    // Notification bell
    const notificationBell = document.getElementById('notificationBell');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationBadge = document.getElementById('notificationBadge');

    if (notificationBell && notificationDropdown) {
        notificationBell.addEventListener('click', function (e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');

            if (notificationDropdown.classList.contains('show') && notificationBadge) {
                notificationBadge.textContent = '0';
                notificationBadge.style.display = 'none';
            }
        });

        document.addEventListener('click', function (e) {
            if (!notificationDropdown.contains(e.target) && !notificationBell.contains(e.target)) {
                notificationDropdown.classList.remove('show');
            }
        });
    }

    // Reject modal
    const approveModal = document.getElementById('approveOrderModal');
    const approveButtons = document.querySelectorAll('.btn-open-approve-modal');
    const cancelApproveModal = document.getElementById('cancelApproveModal');
    const confirmApproveModal = document.getElementById('confirmApproveModal');
    const approveOrderModalMessage = document.getElementById('approveOrderModalMessage');

    const rejectModal = document.getElementById('rejectOrderModal');
    const rejectButtons = document.querySelectorAll('.btn-open-reject-modal');
    const cancelRejectModal = document.getElementById('cancelRejectModal');
    const confirmRejectModal = document.getElementById('confirmRejectModal');
    const rejectReasonText = document.getElementById('reject_reason_text');
    const rejectReasonError = document.getElementById('rejectReasonError');
    const rejectReasonWordCount = document.getElementById('rejectReasonWordCount');

    let activeRejectForm = null;
    let activeApproveForm = null;
    let isApproveSubmitInProgress = false;
    let isRejectSubmitInProgress = false;
    function openApproveModal(orderNumber) {
        if (approveOrderModalMessage) {
            approveOrderModalMessage.textContent = orderNumber
                ? `Are you sure you want to approve order ${orderNumber}?`
                : 'Are you sure you want to approve this order?';
        }
        if (approveModal) {
            approveModal.setAttribute('aria-hidden', 'false');
        }
    }

    function closeApproveModal() {
        if (approveModal) {
            approveModal.setAttribute('aria-hidden', 'true');
        }
        activeApproveForm = null;
    }


    const bannedWords = [
        'idiot',
        'stupid',
        'dumb',
        'hate',
        'useless',
        'shut up',
        'moron',
        'trash',
        'nonsense',
        'garbage',
        'kill',
        'die',
        'fool',
        'jerk',
        'loser'
    ];

    function openRejectModal() {
        if (rejectModal) {
            rejectModal.setAttribute('aria-hidden', 'false');
        }
    }

    function closeRejectModal() {
        if (rejectModal) {
            rejectModal.setAttribute('aria-hidden', 'true');
        }
        if (rejectReasonText) {
            rejectReasonText.value = '';
        }
        if (rejectReasonError) {
            rejectReasonError.style.display = 'none';
            rejectReasonError.textContent = 'Please enter a rejection reason.';
        }
        if (rejectReasonWordCount) {
            rejectReasonWordCount.textContent = '0 / 500 words';
        }
        activeRejectForm = null;
    }

    function getWordCount(text) {
        const trimmed = text.trim();
        if (!trimmed) return 0;
        return trimmed.split(/\s+/).length;
    }

    function containsBannedWords(text) {
        const lowerText = text.toLowerCase();
        return bannedWords.some(word => lowerText.includes(word));
    }

    rejectButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            activeRejectForm = button.closest('.reject-order-form');
            openRejectModal();
        });
    });

    approveButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            activeApproveForm = button.closest('.approve-order-form');
            const orderNumber = button.dataset.orderNumber || '';
            openApproveModal(orderNumber);
        });
    });

    if (cancelApproveModal) {
        cancelApproveModal.addEventListener('click', function () {
            closeApproveModal();
        });
    }

    if (confirmApproveModal) {
        confirmApproveModal.addEventListener('click', function () {
            if (!activeApproveForm) return;
            if (isApproveSubmitInProgress) return;
            isApproveSubmitInProgress = true;
            confirmApproveModal.disabled = true;

            let approveFlagInput = activeApproveForm.querySelector('input[name="approve_order"]');
            if (!approveFlagInput) {
                approveFlagInput = document.createElement('input');
                approveFlagInput.type = 'hidden';
                approveFlagInput.name = 'approve_order';
                approveFlagInput.value = '1';
                activeApproveForm.appendChild(approveFlagInput);
            }

            activeApproveForm.submit();
        });
    }

    if (approveModal) {
        approveModal.addEventListener('click', function (e) {
            if (e.target === approveModal) {
                closeApproveModal();
            }
        });
    }

    if (cancelRejectModal) {
        cancelRejectModal.addEventListener('click', function () {
            closeRejectModal();
        });
    }

    if (rejectModal) {
        rejectModal.addEventListener('click', function (e) {
            if (e.target === rejectModal) {
                closeRejectModal();
            }
        });
    }

    if (rejectReasonText && rejectReasonWordCount) {
        rejectReasonText.addEventListener('input', function () {
            const wordCount = getWordCount(rejectReasonText.value);
            rejectReasonWordCount.textContent = `${wordCount} / 500 words`;

            if (rejectReasonError) {
                rejectReasonError.style.display = 'none';
                rejectReasonError.textContent = 'Please enter a rejection reason.';
            }
        });
    }

    if (confirmRejectModal) {
        confirmRejectModal.addEventListener('click', function () {
            if (!activeRejectForm) return;
            if (isRejectSubmitInProgress) return;

            const reason = rejectReasonText.value.trim();
            const wordCount = getWordCount(reason);

            if (!reason) {
                if (rejectReasonError) {
                    rejectReasonError.textContent = 'Please enter a rejection reason.';
                    rejectReasonError.style.display = 'block';
                }
                return;
            }

            if (wordCount > 500) {
                if (rejectReasonError) {
                    rejectReasonError.textContent = 'Rejection reason must not exceed 500 words.';
                    rejectReasonError.style.display = 'block';
                }
                return;
            }

            if (containsBannedWords(reason)) {
                if (rejectReasonError) {
                    rejectReasonError.textContent = 'Please use professional and respectful language in the rejection reason.';
                    rejectReasonError.style.display = 'block';
                }
                return;
            }

            isRejectSubmitInProgress = true;
            confirmRejectModal.disabled = true;

            const hiddenReasonInput = activeRejectForm.querySelector('input[name="rejection_reason"]');
            if (hiddenReasonInput) {
                hiddenReasonInput.value = reason;
            }

            let rejectFlagInput = activeRejectForm.querySelector('input[name="reject_order"]');
            if (!rejectFlagInput) {
                rejectFlagInput = document.createElement('input');
                rejectFlagInput.type = 'hidden';
                rejectFlagInput.name = 'reject_order';
                rejectFlagInput.value = '1';
                activeRejectForm.appendChild(rejectFlagInput);
            }

            activeRejectForm.submit();
        });
    }
});