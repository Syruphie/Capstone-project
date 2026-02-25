const button = document.getElementById("chatbot-button");
const windowBox = document.getElementById("chatbot-window");
const closeBtn = document.getElementById("chatbot-close");
const sendBtn = document.getElementById("chatbot-send");
const input = document.getElementById("chatbot-input");
const messages = document.getElementById("chatbot-messages");

/* Open chatbot */
button.onclick = () => {
    windowBox.style.display = "flex";
    button.style.display = "none";
};

/* Close chatbot */
closeBtn.onclick = () => {
    windowBox.style.display = "none";
    button.style.display = "flex";
};

/* Send message button */
sendBtn.onclick = sendMessage;

/* Send message function (manual typing) */
function sendMessage() {
    const text = input.value.trim();
    if (!text) return;

    messages.innerHTML += `<div class="chat user"><b>You:</b> ${text}</div>`;
    input.value = "";

    fetch("chatbot/chat_api.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "message=" + encodeURIComponent(text)
    })
    .then(res => res.text())
    .then(reply => {
        messages.innerHTML += `<div class="chat bot"><b>Bot:</b> ${reply}</div>`;
        messages.scrollTop = messages.scrollHeight;
    });
}

/* -----------------------------------
   STEP 3: Quick Questions Click Logic
------------------------------------ */

const quickQuestions = document.querySelectorAll(".question");

quickQuestions.forEach(question => {
    question.addEventListener("click", () => {
        const text = question.innerText;

        // User message
        messages.innerHTML += `<div class="chat user"><b>You:</b> ${text}</div>`;

        let reply = "";

        if (text.includes("approve")) {
            reply = "Go to the Approvals tab from the top menu to review and approve orders.";
        } 
        else if (text.includes("users")) {
            reply = "Click on the Users tab to manage user accounts and permissions.";
        } 
        else if (text.includes("equipment")) {
            reply = "Use the Equipment tab to add, update, or view lab equipment.";
        } 
        else if (text.includes("reports")) {
            reply = "Reports are available under the Reports tab in the navigation bar.";
        } 
        else if (text.includes("logout")) {
            reply = "Click the Logout button in the top-right corner of the dashboard.";
        }

        // Bot reply
        messages.innerHTML += `<div class="chat bot"><b>Bot:</b> ${reply}</div>`;
        messages.scrollTop = messages.scrollHeight;

        // Optional: hide questions after first click
        document.getElementById("quick-questions").style.display = "none";
    });
});
