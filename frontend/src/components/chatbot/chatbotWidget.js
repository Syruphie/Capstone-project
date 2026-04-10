/**
 * Global chatbot widget — included from layout (e.g. header). Not a route page.
 */
function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('chatbot-button');
    const windowBox = document.getElementById('chatbot-window');
    const closeBtn = document.getElementById('chatbot-close');
    const messages = document.getElementById('chatbot-messages');
    const input = document.getElementById('chatbot-input');
    const sendBtn = document.getElementById('chatbot-send');

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

    if (sendBtn && input) {
        sendBtn.addEventListener('click', function () {
            handleUserMessage();
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleUserMessage();
            }
        });
    }

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

        let userQuestion = '';

        switch (type) {
            case 'approve':
                userQuestion = 'How do I approve orders?';
                break;
            case 'newOrder':
                userQuestion = 'How do I create a new order?';
                break;
            case 'equipment':
                userQuestion = 'Where can I manage equipment?';
                break;
            case 'logout':
                userQuestion = 'How do I logout?';
                break;
            default:
                return;
        }

        addUserMessage(userQuestion);
        fetchBotReply(userQuestion);
    }

    function handleUserMessage() {
        if (!input) return;

        const text = input.value.trim();
        if (!text) return;

        addUserMessage(text);
        input.value = '';
        fetchBotReply(text);
    }

    function fetchBotReply(text) {
    showTyping();

    fetch('/Capstone-project/Capstone-project/public/pages/ai_reply.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ message: text })
    })
        .then(function (res) {
            if (!res.ok) {
                throw new Error('HTTP error ' + res.status);
            }
            return res.json();
        })
        .then(function (data) {
            removeTyping();
            addBotMessage(data.reply || 'Sorry, I could not process that.');
            showOptions();
        })
        .catch(function (error) {
            removeTyping();
            console.error('AI fetch error:', error);
            addBotMessage('Error connecting to the AI assistant.');
            showOptions();
        });
}

    function addBotMessage(text) {
        messages.insertAdjacentHTML(
            'beforeend',
            '<div class="bot-message"><strong>Bot:</strong> ' + escapeHtml(text) + '</div>'
        );
        messages.scrollTop = messages.scrollHeight;
    }

    function addUserMessage(text) {
        messages.insertAdjacentHTML(
            'beforeend',
            '<div class="user-message"><strong>You:</strong> ' + escapeHtml(text) + '</div>'
        );
        messages.scrollTop = messages.scrollHeight;
    }

    function showTyping() {
        removeTyping();
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