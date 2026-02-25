<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Chatbot Button -->
<div id="chatbot-button">
    <i class="fa-solid fa-comments"></i>
</div>

<!-- Chat Window -->
<div id="chatbot-window">
    <div id="chatbot-header">
        <span>Virtual Assistant</span>
        <span id="chatbot-close">&times;</span>
    </div>

    <!-- Chat Messages -->
    <div id="chatbot-messages">

        <!-- Quick Navigation Questions -->
        <div id="quick-questions">
            <div class="question">How do I approve orders?</div>
            <div class="question">Where can I manage users?</div>
            <div class="question">How do I manage equipment?</div>
            <div class="question">Where can I view reports?</div>
            <div class="question">How do I logout?</div>
        </div>

    </div>

    <!-- Input Area -->
    <div id="chatbot-input-area">
        <input type="text" id="chatbot-input" placeholder="Ask me something...">
        <button id="chatbot-send">Send</button>
    </div>
</div>

<!-- Chatbot CSS & JS -->
<link rel="stylesheet" href="chatbot/chat.css">
<script src="chatbot/chat.js"></script>
