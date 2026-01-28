# PHP_Laravel12_WebSockets_Using_Laravel_Soketi

WebSockets using Laravel 12 and Soketi

---

## Project Overview

This project demonstrates how to implement real-time WebSocket communication using **Laravel 12** and **Soketi**. It provides a simple and beginner-friendly example where a message is broadcast from the backend and received instantly on the frontend without any page reload.

The main goal of this project is to help developers clearly understand **Laravel Broadcasting**, **WebSockets**, and **Soketi** using a practical and minimal local setup.

---

## Features

* Real-time message broadcasting
* Laravel 12 broadcasting system
* Soketi WebSocket server
* Laravel Echo with Pusher protocol
* Simple and testable example
* No third-party paid services
* Local development friendly

---

## Technology Stack

* PHP 8.2+
* Laravel 12
* Soketi
* Laravel Echo
* Pusher JavaScript SDK
* Node.js and NPM
* Vite

---

## Project Structure

```
PHP_Laravel12_WebSockets_Using_Laravel_Soketi
├── app
│   └── Events
│       └── MessageSent.php
├── resources
│   ├── js
│   │   └── bootstrap.js
│   └── views
│       └── chat.blade.php
├── routes
│   └── web.php
├── config
│   └── broadcasting.php
├── .env
└── README.md
```

---

## Prerequisites

Before starting, make sure the following are installed:

* PHP 8.2 or higher
* Composer
* Node.js
* NPM
* Laravel CLI

---

## Installation Steps

### Step 1: Create Laravel Project

```bash
composer create-project laravel/laravel PHP_Laravel12_WebSockets_Using_Laravel_Soketi
cd PHP_Laravel12_WebSockets_Using_Laravel_Soketi
```

---

### Step 2: Install Required Packages

```bash
composer require pusher/pusher-php-server
npm install laravel-echo pusher-js
```

---

### Step 3: Install Soketi

```bash
npm install -g @soketi/soketi
```

Verify installation:

```bash
soketi --version
```

---

## Environment Configuration

Update your `.env` file with the following values:

```
BROADCAST_CONNECTION=pusher

PUSHER_APP_ID=local
PUSHER_APP_KEY=local
PUSHER_APP_SECRET=local
PUSHER_APP_CLUSTER=mt1

PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
```

---

## Broadcasting Configuration

Edit `config/broadcasting.php` and update the pusher options:

```php
'options' => [
    'cluster' => env('PUSHER_APP_CLUSTER'),
    'host' => env('PUSHER_HOST'),
    'port' => env('PUSHER_PORT'),
    'scheme' => env('PUSHER_SCHEME'),
    'useTLS' => false,
],
```

---

## Creating Broadcast Event

Generate the event:

```bash
php artisan make:event MessageSent
```

Edit `app/Events/MessageSent.php`:

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use SerializesModels;

    public string $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new Channel('chat-channel');
    }
}
```

---

## Routes Setup

Edit `routes/web.php`:

```php
use App\Events\MessageSent;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('chat');
});

Route::get('/send-message', function () {
    broadcast(new MessageSent('Hello from Laravel WebSocket!'));
    return 'Message Sent';
});
```

---

## Frontend WebSocket Setup

### Laravel Echo Configuration

Edit `resources/js/bootstrap.js`:

```js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'local',
    wsHost: '127.0.0.1',
    wsPort: 6001,
    forceTLS: false,
    disableStats: true,
});
```

---

### Create View

Create `resources/views/chat.blade.php`:

```html
<!DOCTYPE html>
<html>
<head>
    <title>Laravel 12 WebSockets Test</title>
    @vite(['resources/js/app.js'])
</head>
<body>

<h2>WebSocket Message</h2>
<p id="message">Waiting for message...</p>

<script>
    window.Echo.channel('chat-channel')
        .listen('MessageSent', (e) => {
            document.getElementById('message').innerText = e.message;
        });
</script>

</body>
</html>
```

---

## Build Frontend Assets

```bash
npm install
npm run dev
```

---

## Running the Application

### Start Soketi Server

```bash
soketi start
```

Soketi will run on port **6001**.

---

### Start Laravel Server

```bash
php artisan serve
```

---

## Testing the WebSocket

Open browser and visit:

```
http://127.0.0.1:8000
```

Open another tab and visit:

```
http://127.0.0.1:8000/send-message
```

The message will appear instantly on the first page, confirming that WebSocket communication is working successfully.

### Screenshort
<img width="612" height="267" alt="image" src="https://github.com/user-attachments/assets/8981f94b-57d7-4a97-8936-a4fb35f7c598" />
<img width="1919" height="382" alt="image" src="https://github.com/user-attachments/assets/185ab3c8-0210-471b-9201-897e5edf3bba" />

---

## Application Flow

```
HTTP Request
   ↓
Laravel Route
   ↓
Broadcast Event
   ↓
Soketi WebSocket Server
   ↓
Laravel Echo (Browser)
```

---

## Use Cases

* Real-time chat systems
* Live notifications
* Online user presence
* Real-time dashboards
* Collaborative applications

---

## Common Issues

* Ensure Soketi is running before testing
* Make sure port 6001 is not blocked
* Run `npm run dev` after updating JavaScript files
* Verify broadcasting configuration in `.env`

---

## Future Enhancements

* Private and presence channels
* User authentication with WebSockets
* Real-time chat UI
* Notification broadcasting
* Queue-based broadcasting

---

## Author

**Mihir Mehta**
PHP Laravel Developer

---

## License

This project is open-source and available for learning and educational purposes.
