<!DOCTYPE html>
<html>

<head>

    <title>
        Laravel 12 Reverb Notifications
    </title>

    @vite(['resources/js/app.js'])


    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {

            font-family: "Segoe UI", Arial, sans-serif;

            background: linear-gradient(135deg,
                    #667eea,
                    #764ba2);

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 30px;

        }

        .container-box {

            width: 600px;

            background: white;

            border-radius: 20px;

            padding: 30px;

            box-shadow:
                0 20px 40px rgba(0, 0, 0, .2);

        }

        h2 {

            text-align: center;

            color: #4f46e5;

            margin-bottom: 25px;

        }

        .form-group {

            margin-bottom: 15px;

        }

        label {

            display: block;

            margin-bottom: 6px;

            font-weight: 600;

        }

        input,
        textarea,
        select {


            width: 100%;

            padding: 12px;

            border: 1px solid #ddd;

            border-radius: 10px;

            outline: none;

            font-size: 14px;

        }

        textarea {

            resize: none;

            height: 100px;

        }

        input:focus,
        textarea:focus,
        select:focus {

            border-color: #4f46e5;

        }

        button {


            width: 100%;

            padding: 13px;

            border: none;

            background: #4f46e5;

            color: white;

            border-radius: 10px;

            cursor: pointer;

            font-size: 16px;

        }

        button:hover {

            background: #3730a3;

        }

        .notification-box {


            margin-top: 30px;

        }

        .notification {


            padding: 15px;

            border-radius: 12px;

            margin-bottom: 12px;

            background: #f8fafc;

            border-left: 5px solid #4f46e5;

        }

        .notification h4 {

            margin-bottom: 5px;

        }

        .notification small {

            color: #64748b;

        }

        .success {

            border-color: #16a34a;

        }

        .info {

            border-color: #2563eb;

        }

        .warning {

            border-color: #ca8a04;

        }

        .danger {

            border-color: #dc2626;

        }
    </style>


</head>


<body>


    <div class="container-box">


        <h2>
            🔔 Real-Time Notification Dashboard
        </h2>


        <form id="notificationForm">


            <div class="form-group">

                <label>
                    Notification Title
                </label>


                <input
                    type="text"
                    id="title"
                    placeholder="Enter title">

            </div>

            <div class="form-group">


                <label>
                    Message
                </label>


                <textarea
                    id="message"
                    placeholder="Enter notification message"></textarea>


            </div>

            <div class="form-group">


                <label>
                    Notification Type
                </label>


                <select id="type">


                    <option value="success">
                        Success
                    </option>


                    <option value="info">
                        Info
                    </option>


                    <option value="warning">
                        Warning
                    </option>


                    <option value="danger">
                        Danger
                    </option>


                </select>


            </div>


            <button type="submit">

                Send Notification

            </button>

        </form>


        <div class="notification-box">


            <h3>
                Notification History
            </h3>


            <div id="notificationList">


                @foreach($notifications as $notification)


                <div class="notification {{ $notification->type }}">


                    <h4>
                        {{ $notification->title }}
                    </h4>


                    <p>
                        {{ $notification->message }}
                    </p>


                    <small>
                        {{ $notification->created_at }}
                    </small>


                </div>


                @endforeach


            </div>


        </div>



    </div>

    <script>
        document
            .getElementById('notificationForm')
            .addEventListener('submit', function(e) {

                e.preventDefault();

                let title =
                    document.getElementById('title').value;


                let message =
                    document.getElementById('message').value;


                let type =
                    document.getElementById('type').value;


                fetch('/notifications', {


                        method: 'POST',


                        headers: {


                            'Content-Type': 'application/json',


                            'X-CSRF-TOKEN': '{{ csrf_token() }}'


                        },


                        body: JSON.stringify({


                            title: title,

                            message: message,

                            type: type


                        })


                    })

                    .then(response => response.json())

                    .then(data => {


                        console.log(
                            "Notification sent:",
                            data
                        );


                        document
                            .getElementById('title')
                            .value = "";


                        document
                            .getElementById('message')
                            .value = "";


                    })

                    .catch(error => {


                        console.error(
                            error
                        );


                    });



            });


        // Listen for Reverb Notification Event

        document.addEventListener(
            "DOMContentLoaded",
            function() {


                if (window.Echo) {


                    console.log(
                        "Reverb Echo Connected"
                    );



                    window.Echo

                        .channel('notifications')

                        .listen(
                            '.notification.sent',
                            (e) => {



                                console.log(
                                    "New Notification:",
                                    e
                                );



                                let notification =
                                    e.notification;



                                let html = `


<div class="notification ${notification.type}">


<h4>

${notification.title}

</h4>


<p>

${notification.message}

</p>


<small>

Just now

</small>


</div>



`;


                                document
                                    .getElementById('notificationList')
                                    .insertAdjacentHTML(
                                        'afterbegin',
                                        html
                                    );



                            });



                } else {


                    console.error(
                        "Echo not loaded"
                    );


                }

            });
    </script>

</body>

</html>