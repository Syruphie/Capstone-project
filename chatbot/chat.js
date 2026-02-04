const button = document.getElementById("chatbot-button");
const windowBox = document.getElementById("chatbot-window");
const closeBtn = document.getElementById("chatbot-close");
const sendBtn = document.getElementById("chatbot-send");
const input = document.getElementById("chatbot-input");
const messages = document.getElementById("chatbot-messages");

/* Open chatbot */
button.onclick = () => {
    windowBox.style.display = "flex";
    button.style.display = "none"; // 🔥 hide button
};

/* Close chatbot */
closeBtn.onclick = () => {
    windowBox.style.display = "none";
    button.style.display = "flex"; // 🔥 show button again
};

sendBtn.onclick = sendMessage;

function sendMessage() {
    const text = input.value.trim();
    if (!text) return;

    messages.innerHTML += `<div><b>You:</b> ${text}</div>`;
    input.value = "";

    fetch("chatbot/chat_api.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "message=" + encodeURIComponent(text)
    })
    .then(res => res.text())
    .then(reply => {
        messages.innerHTML += `<div><b>Bot:</b> ${reply}</div>`;
        messages.scrollTop = messages.scrollHeight;
    });
}
