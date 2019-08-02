<?php

namespace App\Http\Controllers;

use App\Events\UserRegistered;
use Illuminate\Http\Request;
use App\User;
use Laravel\Socialite\Facades\Socialite;
use Auth;
use Exception;

class SocialAuthGoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $existUser = User::where('email', $googleUser->email)->first();
            if ($existUser) {
                $existUser->color = User::getRandColor();
                $existUser->save();         //if user exists in db - give him new color and save changes, then log in into account
                Auth::loginUsingId($existUser->id);
            } else {
                $user = new User;
                $user->name = $googleUser->name;
                $user->email = $googleUser->email;
                $user->google_id = $googleUser->id;     //if user doesnt exists , create a new one and save to database
                $user->gravatar_img = md5($googleUser->email);
                $user->color = User::getRandColor();
                $user->save();
                broadcast(new UserRegistered($user));       //start broadcasting userRegistered event
                Auth::loginUsingId($user->id);
            }
            return redirect()->to('/chat');  //redirect to a chat
        } catch (Exception $e) {
            return 'error';
        }
    }
}
