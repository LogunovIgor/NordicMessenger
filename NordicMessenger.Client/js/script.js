let currentMessages = [];

let url = 'https://0qd0ku94.ddns.net/php/';

function bringScrollToBottom() {
    let messageBlock = document.getElementById('input-messages');
    messageBlock.scrollTop = messageBlock.scrollHeight;
}


function sendMessage() {
    let message = document.querySelector('.output-message-area').value.trim();

    if (message == null || message.length <= 0)
        return false;

    let body = 'message=' + encodeURIComponent(message);
    let request = new XMLHttpRequest();
    request.open('POST', url, true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    request.send(body);

    getAllMessages();
    return true;
}

function updateMessageContent() {
    let newMessages = getNewMessages();
    let messageBlock = document.getElementById('input-messages');

    if (messageBlock == null || newMessages == null) 
        return;

    let isScrollBottom = messageBlock.scrollHeight - messageBlock.scrollTop - messageBlock.clientHeight <= 0;

    for(let i = 0; i < newMessages.length; i++) {
        messageBlock.innerHTML += `<div><div class="input-message">${newMessages[i].content}</div></div>`;
    }

    if (isScrollBottom) 
        bringScrollToBottom();
}


function getNewMessages() {
    let result = [];
    let inputMessages = getAllMessages();

    for(let i = currentMessages.length; i < inputMessages.length; i++) {
        result.push(inputMessages[i]); 
    }

    currentMessages = inputMessages;

    return result;
}

function getAllMessages() {
    let request = new XMLHttpRequest();
    request.open('GET', url, false);
    request.send();

    return JSON.parse(request.responseText);
}

function onPressEnter() {
    let key = window.event.keyCode;

    if (key != 13)
        return true;

    if (sendMessage())
        clearMessageArea();

    window.event.preventDefault();
    return false;
}

function clearMessageArea() {
    let messageArea = document.querySelector('.output-message-area');
    messageArea.value = '';
}

setInterval(() => {
    updateMessageContent();    
}, 1000);