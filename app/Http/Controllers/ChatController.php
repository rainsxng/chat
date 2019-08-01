<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\UserBanned;
use App\Events\UserMuted;
use App\Events\UserUnbanned;
use App\Events\UserUnmuted;
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
        if (auth() ->user()->isBanned) {
            return view('chat.ban');
        }
        else
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
        $user = User::findOrFail($request->input('user.id'));
        $user->isBanned = !$user->isBanned;
        $user->save();
        broadcast(new UserBanned($user))->toOthers();
        return ['status' => 'success'];
    }

    public function muteUser(Request $request)
    {
        $user = User::findOrFail($request->input('user.id'));
        $user->isMuted = !$user->isMuted;
        $user->save();
        broadcast(new UserMuted($user))->toOthers();
        return ['status' => 'success'];
    }
}
