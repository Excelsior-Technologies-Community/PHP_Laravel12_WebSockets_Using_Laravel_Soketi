<!DOCTYPE html>
<html>

<head>

    <title>
        Laravel 12 Reverb Chat
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

            height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

        }



        .chat-container {


            width: 450px;

            height: 650px;

            background: #fff;

            border-radius: 20px;

            overflow: hidden;

            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);

            display: flex;

            flex-direction: column;

        }



        .chat-header {


            background: #4f46e5;

            color: white;

            padding: 20px;

            text-align: center;


        }



        .chat-header h2 {

            font-size: 24px;

            margin-bottom: 5px;

        }



        .chat-header span {

            font-size: 13px;

            opacity: .8;

        }



        #messages {


            flex: 1;

            padding: 20px;

            overflow-y: auto;

            background: #f8fafc;


        }



        .message {


            background: white;

            padding: 12px 15px;

            margin-bottom: 12px;

            border-radius: 15px;

            box-shadow: 0 3px 8px rgba(0, 0, 0, .08);


        }



        .message b {


            color: #4f46e5;

            font-size: 15px;


        }



        .message small {


            display: block;

            margin-top: 5px;

            color: #64748b;

            font-size: 12px;


        }




        .chat-footer {


            padding: 15px;

            background: white;

            border-top: 1px solid #eee;


        }




        input {


            width: 100%;

            padding: 12px;

            margin-bottom: 10px;

            border-radius: 10px;

            border: 1px solid #ddd;

            outline: none;

            font-size: 14px;


        }



        input:focus {

            border-color: #4f46e5;

        }





        button {


            width: 100%;

            padding: 12px;

            border: none;

            background: #4f46e5;

            color: white;

            border-radius: 10px;

            cursor: pointer;

            font-size: 16px;

            transition: .3s;


        }



        button:hover {


            background: #3730a3;


        }




        ::-webkit-scrollbar {

            width: 6px;

        }


        ::-webkit-scrollbar-thumb {

            background: #cbd5e1;

            border-radius: 10px;

        }



        @media(max-width:500px) {


            .chat-container {

                width: 95%;

                height: 90vh;

            }

        }
    </style>


</head>



<body>



    <div class="chat-container">



        <div class="chat-header">


            <h2>
                Laravel 12 Reverb Chat
            </h2>


            <span>
                Real-Time Messaging
            </span>


        </div>





        <div id="messages">



            @foreach($messages as $msg)


            <div class="message">


                <b>
                    {{ $msg->name }}
                </b>


                :

                {{ $msg->message }}



                <small>

                    {{ $msg->created_at }}

                </small>



            </div>


            @endforeach



        </div>






        <div class="chat-footer">



            <input
                id="name"
                placeholder="Enter your name">



            <input
                id="message"
                placeholder="Type your message...">



            <button onclick="sendMessage()">

                Send Message

            </button>



        </div>




    </div>







    <script>
        function sendMessage() {


            let name =
                document.getElementById('name').value;


            let message =
                document.getElementById('message').value;



            if (!name || !message) {

                alert("Please enter name and message");

                return;

            }




            fetch('/send-message', {


                method: 'POST',


                headers: {


                    'Content-Type': 'application/json',


                    'X-CSRF-TOKEN': '{{ csrf_token() }}'


                },


                body: JSON.stringify({


                    name: name,


                    message: message


                })


            });



            document.getElementById('message').value = "";


        }








        document.addEventListener(
            "DOMContentLoaded",

            function() {



                if (window.Echo) {



                    console.log(
                        "Echo Loaded Successfully"
                    );



                    window.Echo

                        .channel('chat')

                        .listen(
                            '.message.sent',

                            (e) => {



                                console.log(
                                    "Received:",
                                    e
                                );



                                let msg = e.message;



                                document
                                    .getElementById('messages')
                                    .innerHTML += `


<div class="message">


<b>
${msg.name}
</b>


:

${msg.message}


<small>
Just now
</small>


</div>



`;



                                let box =
                                    document.getElementById('messages');


                                box.scrollTop =
                                    box.scrollHeight;



                            }

                        );



                } else {


                    console.error(
                        "Echo not loaded"
                    );


                }



            });
    </script>



</body>

</html>