<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware( 'guest' )->except( 'logout' );
    }

    /**
     * Override the trait's method to customize the redirect path after a successful login
     * @hooked valpress/after-login/redirect-path
     * @return mixed|string
     */
    public function redirectTo()
    {
        $user = auth()->user();
        if ( !$user ) {
            return $this->redirectTo;
        }
        return $this->redirectTo = apply_filters( 'valpress/after-login/redirect-path', $user );
    }

}
