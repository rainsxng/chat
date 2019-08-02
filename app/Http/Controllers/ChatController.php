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
        $this->middleware('auth');      //everyone who request this controller should be authorized
    }

    public function index()
    {
        if  ( auth()->user()->isAdmin()) {  //if user is admin - get all users from database and pass it to view
            return view('chat.index')->with('users', User::where('role', '!=', 'admin')->get());
        }
        if (auth() ->user()->isBanned) {
            return view('chat.ban');    //if user is banned = return view that doesnt connect to websockets
        }
        return view('chat.index');      //for regular user - return chat view
    }

    public function fetchMessages()
    {
        if (!auth()->user()->isBanned) {
            return Message::with('user')->get();   //if user isn't banned - return all messages from database with users who wrote them
        }
        return \response('You was banned by admin', 403);       //else return error
    }

    public function sendMessage(MessageSendMessageRequest $request)
    {
        $lastMessageDateInString = Message::where('user_id', '=', auth()->user()->id)->get(['created_at'])->last(); //get last message from user
        if (empty($lastMessageDateInString))
            $difference = 20;   //if there's no messages, let him write first
        else {
            $lastMessageDate = Carbon::parse($lastMessageDateInString->created_at);    //else check for 15 sec window between messages
            $difference = Carbon::now()->diffInSeconds($lastMessageDate);
        }


        if (!auth()->user()->isBanned && !auth()->user()->isMuted) {
                //if user isn't banned or muted , and wrote last message more then 15 sec ago - let him write one more
            if ($difference > 15) {
                $message = auth()->user()->messages()->create([
                    'message' => $request->message
                ]);

                broadcast(new MessageSent($message->load('user')))->toOthers(); //broadcast new 'MessageSent' event to other users

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
            $user = User::findOrFail($request->input('user.id'));   //check if user have permission to ban user
            $user->isBanned = !$user->isBanned;
            $user->save();      //ban/unban user and start broadcast 'UserBanned' event
            broadcast(new UserBanned($user));
            return ['status' => 'success'];
        }
        return \response('You cannot perform this action', 403);
    }

    public function muteUser(Request $request)
    {
        if (auth()->user()->isAdmin()) { //check if user have permission to mute user
            $user = User::findOrFail($request->input('user.id'));
            $user->isMuted = !$user->isMuted;
            $user->save();    //mute/unmute user and start broadcast 'UserMuted' event
            broadcast(new UserMuted($user));
            return ['status' => 'success'];
        }
        return \response('You cannot perform this action', 403);
    }
}
