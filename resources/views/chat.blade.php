<!DOCTYPE html>
<html>
<head>
    <title>Laravel 12 WebSockets Test</title>
    @vite(['resources/js/app.js'])
</head>
<body>

<h2>WebSocket Message:</h2>
<p id="message">Waiting...</p>

<script>
    window.Echo.channel('chat-channel')
        .listen('MessageSent', (e) => {
            document.getElementById('message').innerText = e.message;
        });
</script>

</body>
</html>
