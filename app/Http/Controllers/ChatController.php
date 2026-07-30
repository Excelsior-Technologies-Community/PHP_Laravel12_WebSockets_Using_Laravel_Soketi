<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;


class ChatController extends Controller
{


    public function index()
    {

        $messages = Message::latest()
            ->get()
            ->reverse();


        return view('chat', compact('messages'));

    }



    public function send(Request $request)
    {

        $request->validate([

            'name'=>'required',

            'message'=>'required'

        ]);



        $message = Message::create([

            'name'=>$request->name,

            'message'=>$request->message

        ]);



        broadcast(new MessageSent($message));



        return response()->json([

            'status'=>'success',

            'data'=>$message

        ]);

    }


}