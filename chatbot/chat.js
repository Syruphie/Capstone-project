document.addEventListener("DOMContentLoaded", () => {

    const button = document.getElementById("chatbot-button");
    const windowBox = document.getElementById("chatbot-window");
    const closeBtn = document.getElementById("chatbot-close");
    const messages = document.getElementById("chatbot-messages");

    // OPEN chatbot
    button.addEventListener("click", () => {
        windowBox.style.display = "flex";
        button.style.display = "none"; 
        messages.innerHTML = "";
        botGreeting();
    });

    // CLOSE chatbot
    closeBtn.addEventListener("click", () => {
        windowBox.style.display = "none";
        button.style.display = "flex";
    });

    /* ---------------- BOT FUNCTIONS ---------------- */

    function botGreeting() {
        showTyping();

        setTimeout(() => {
            removeTyping();
            addBotMessage("Hi! How can I help you today?");
            showOptions();
        }, 1200);
    }

    function showOptions() {
        const optionsHTML = `
            <div class="chat-options">
                <button onclick="handleOption('approve')">How do I approve orders?</button>
                <button onclick="handleOption('newOrder')">How do I create a new order?</button>
                <button onclick="handleOption('equipment')">Where can I manage equipment?</button>
                <button onclick="handleOption('logout')">How do I logout?</button>
            </div>
        `;
        messages.innerHTML += optionsHTML;
        messages.scrollTop = messages.scrollHeight;
    }

    window.handleOption = function(type) {
        document.querySelector(".chat-options")?.remove();

        let reply = "";

        switch (type) {
            case "approve":
                reply = "To approve orders, open the Approvals tab from the top navigation. This option is available only for admin users.";
                break;

            case "newOrder":
                reply = "You can create a new order from the Dashboard. Look for the New Order option.";
                break;

            case "equipment":
                reply = "Equipment management is available in the Admin panel under the Equipment section.";
                break;

            case "logout":
                reply = "The logout option is located at the top-right corner of the application.";
                break;
        }

        showTyping();

        setTimeout(() => {
            removeTyping();
            addBotMessage(reply);
            showOptions();
        }, 1000);
    };

    /* ---------------- UI HELPERS ---------------- */

    function addBotMessage(text) {
        messages.innerHTML += `
            <div class="bot-message">
                <strong>Bot:</strong> ${text}
            </div>
        `;
        messages.scrollTop = messages.scrollHeight;
    }

    function showTyping() {
        messages.innerHTML += `
            <div class="typing" id="typing">
                Bot is typing<span>.</span><span>.</span><span>.</span>
            </div>
        `;
        messages.scrollTop = messages.scrollHeight;
    }

    function removeTyping() {
        document.getElementById("typing")?.remove();
    }

});