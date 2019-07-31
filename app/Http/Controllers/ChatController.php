<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\UserBanned;
use App\User;
use Illuminate\Http\Request;
use App\Message;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if  ( auth()->user()->isAdmin())
            return view('chat.index')->with('users', User::where('role', '!=','admin')->get());
        if (auth()->user()->isBanned) {
            session()->flash('danger', 'You has been banned at this chat');
            return redirect()->back();
        }
        return view('chat.index');
    }

    public function fetchMessages()
    {
        return Message::with('user')->get();
    }

    public function sendMessage(Request $request)
    {
        $message = auth()->user()->messages()->create([
            'message' => $request->message
        ]);

        broadcast(new MessageSent($message->load('user')))->toOthers();

        return ['status' => 'success'];
    }


    public function banUser(Request $request)
    {
        $user = User::find($request->input('user.id'));
        broadcast(new UserBanned($user))->toOthers();
        return ['status' => 'success'];
    }
}
