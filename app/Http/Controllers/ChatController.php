<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\UserBanned;
use App\Events\UserMuted;
use App\Http\Requests\MessageSendMessageRequest;
use App\User;
use Carbon\Carbon;
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
        if  ( auth()->user()->isAdmin()) {
            $users = User::where('role', '!=', 'admin')->get();

            return view('chat.index')->with('users', $users);
        }
        if (auth() ->user()->isBanned) {
            return view('chat.ban');
        }
        return view('chat.index');
    }

    public function fetchMessages()
    {
        if (!auth()->user()->isBanned) {
            return Message::with('user')->get();
        }
        return \response('You was banned by admin', 403);
    }

    public function sendMessage(MessageSendMessageRequest $request)
    {
        $lastMessageDateInString = Message::where('user_id', '=', auth()->user()->id)->get(['created_at'])->last();
        if (empty($lastMessageDateInString))
            $difference = 20;
        else {
            $lastMessageDate = Carbon::parse($lastMessageDateInString->created_at);
            $difference = Carbon::now()->diffInSeconds($lastMessageDate);
        }


        if (!auth()->user()->isBanned && !auth()->user()->isMuted) {

            if ($difference > 15 ) {
                $message = auth()->user()->messages()->create([
                    'message' => $request->message
                ]);

                broadcast(new MessageSent($message->load('user')))->toOthers();

                return ['status' => 'success'];

            }
            else {
                return \response('Wait for 15 seconds', 400);
            }
        }
        return \response('You cannot perform this action', 403);
    }


    public function banUser(Request $request)
    {
        if (auth()->user()->isAdmin()) {
            $user = User::findOrFail($request->input('user.id'));
            $user->isBanned = !$user->isBanned;
            $user->save();
            broadcast(new UserBanned($user));
            return ['status' => 'success'];
        }
        return \response('You cannot perform this action', 403);
    }

    public function muteUser(Request $request)
    {
        if (auth()->user()->isAdmin()) {
            $user = User::findOrFail($request->input('user.id'));
            $user->isMuted = !$user->isMuted;
            $user->save();
            broadcast(new UserMuted($user));
            return ['status' => 'success'];
        }
        return \response('You cannot perform this action', 403);
    }
}
