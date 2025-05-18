<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Usuario;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
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
        $this->middleware('guest')->except('logout');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'email';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('web');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Si es administrador, redirigir al panel de administración
        if ($user->es_admin) {
            return redirect()->intended('/admin');
        }

        // De lo contrario, redirigir a home
        return redirect()->intended($this->redirectPath());
    }

    /**
     * Redirect the user to the provider authentication page.
     *
     * @param  string $provider
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from the provider.
     *
     * @param  string $provider
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/iniciar-sesion')->with('error', 'Ocurrió un error al autenticar con ' . ucfirst($provider));
        }

        // Buscar usuario existente por email o ID social
        $usuario = Usuario::where('email', $socialUser->getEmail())
                        ->orWhere(function($query) use ($provider, $socialUser) {
                            $query->where('id_social', $socialUser->getId())
                                  ->where('tipo_social', $provider);
                        })
                        ->first();

        // Si no existe, crear nuevo usuario
        if (!$usuario) {
            $usuario = new Usuario();
            $usuario->nombre = $socialUser->getName() ?? $socialUser->getNickname();
            $usuario->email = $socialUser->getEmail();
            $usuario->password = Hash::make(Str::random(24));
            $usuario->id_social = $socialUser->getId();
            $usuario->tipo_social = $provider;
            $usuario->email_verificado_en = now();
            $usuario->save();
        } else {
            // Actualizar información social si el usuario ya existe
            $usuario->id_social = $socialUser->getId();
            $usuario->tipo_social = $provider;
            if (!$usuario->email_verificado_en) {
                $usuario->email_verificado_en = now();
            }
            $usuario->save();
        }

        // Iniciar sesión
        Auth::login($usuario, true);

        return redirect($this->redirectTo);
    }
}
