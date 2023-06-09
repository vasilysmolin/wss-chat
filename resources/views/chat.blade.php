<!DOCTYPE html>
<html>
<head>
    <title>Chat</title>
</head>
<body>
<h1>{{Auth::user()?->email}}</h1>
<div id="messages"></div>
<form>
    <input type="text" id="message" autocomplete="off">
    <button>Send</button>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    var webSocket = new WebSocket('ws://localhost:8086');

    let userId = '{{Auth::user()?->getKey()}}';
    webSocket.addEventListener('open', function (event) {
        // при отправке запроса добавляем заголовок "Authorization"
        webSocket.send(JSON.stringify({
            action: 'open',
            user_id: userId,
        }));
    });

    webSocket.onopen = function(event) {
        console.log('WebSocket is connected.');
    };


    webSocket.onmessage = function(event) {
        var messagesElement = $('#messages');
        messagesElement.append('<div>' + event.data + '</div>');
        messagesElement.scrollTop(messagesElement[0].scrollHeight);
        console.log('WebSocket on message.');
    };

    $('form').submit(function(event) {
        event.preventDefault();
        var message = $('#message').val();
        webSocket.send(message);
        $('#message').val('');
    });
</script>
</body>
</html>
