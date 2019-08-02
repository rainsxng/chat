<?php

namespace App\Http\Controllers;

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
                $existUser->save();
                Auth::loginUsingId($existUser->id);
            } else {
                $user = new User;
                $user->name = $googleUser->name;
                $user->email = $googleUser->email;
                $user->google_id = $googleUser->id;
                $user->color = User::getRandColor();
                $user->save();
                Auth::loginUsingId($user->id);
            }
            return redirect()->to('/chat');
        } catch (Exception $e) {
            return 'error';
        }
    }
}
