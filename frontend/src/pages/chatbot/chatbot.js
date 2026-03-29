document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('chatbot-button');
    const windowBox = document.getElementById('chatbot-window');
    const closeBtn = document.getElementById('chatbot-close');
    const messages = document.getElementById('chatbot-messages');

    if (!button || !windowBox || !closeBtn || !messages) return;

    button.addEventListener('click', function () {
        windowBox.style.display = 'flex';
        button.style.display = 'none';
        messages.innerHTML = '';
        botGreeting();
    });

    closeBtn.addEventListener('click', function () {
        windowBox.style.display = 'none';
        button.style.display = 'flex';
    });

    function botGreeting() {
        showTyping();

        setTimeout(function () {
            removeTyping();
            addBotMessage('Hi! How can I help you today?');
            showOptions();
        }, 1200);
    }

    function showOptions() {
        const optionsHTML =
            '<div class="chat-options">' +
            '<button type="button" data-opt="approve">How do I approve orders?</button>' +
            '<button type="button" data-opt="newOrder">How do I create a new order?</button>' +
            '<button type="button" data-opt="equipment">Where can I manage equipment?</button>' +
            '<button type="button" data-opt="logout">How do I logout?</button>' +
            '</div>';
        messages.insertAdjacentHTML('beforeend', optionsHTML);
        messages.scrollTop = messages.scrollHeight;
        messages.querySelectorAll('.chat-options button').forEach(function (btn) {
            btn.addEventListener('click', function () {
                handleOption(btn.getAttribute('data-opt'));
            });
        });
    }

    function handleOption(type) {
        document.querySelector('.chat-options')?.remove();

        let reply = '';

        switch (type) {
            case 'approve':
                reply =
                    'To approve orders, open the Approvals tab from the top navigation. This option is available only for admin users.';
                break;

            case 'newOrder':
                reply = 'You can create a new order from the Dashboard. Look for the New Order option.';
                break;

            case 'equipment':
                reply = 'Equipment management is available in the Admin panel under the Equipment section.';
                break;

            case 'logout':
                reply = 'The logout option is located at the top-right corner of the application.';
                break;
            default:
                return;
        }

        showTyping();

        setTimeout(function () {
            removeTyping();
            addBotMessage(reply);
            showOptions();
        }, 1000);
    }

    function addBotMessage(text) {
        messages.insertAdjacentHTML(
            'beforeend',
            '<div class="bot-message"><strong>Bot:</strong> ' + escapeHtml(text) + '</div>'
        );
        messages.scrollTop = messages.scrollHeight;
    }

    function escapeHtml(s) {
        const div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }

    function showTyping() {
        messages.insertAdjacentHTML(
            'beforeend',
            '<div class="typing" id="typing">Bot is typing<span>.</span><span>.</span><span>.</span></div>'
        );
        messages.scrollTop = messages.scrollHeight;
    }

    function removeTyping() {
        document.getElementById('typing')?.remove();
    }
});
