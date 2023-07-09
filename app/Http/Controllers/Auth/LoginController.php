<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if(Auth::attempt($credentials, $request->filled('remember'))) {
            $accessToken = $request->user()->createToken('authToken')->plainTextToken;
            $request->session()->regenerate();
            return response()->json([
                'status' => true, 
                'user' => Auth::user(), 
                'access_token' => $accessToken
            ]);
        }
    
        return response()->json(['status' => false, 'message' => 'invalid username or password'], 500);
    }

    public function logout(Request $request)
    {
        $cookie1 = Cookie::forget('laravel_session');
        $cookie2 = Cookie::forget('XSRF-TOKEN');
        $request->user()->tokens()->delete();
        $request->session()->invalidate();
    
        return response()->json(['status' => true, 'm1essage' => 'logged out'])
            ->withCookie($cookie1)->withCookie($cookie2);
    }
}
