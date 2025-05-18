<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Usuario;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SocialAuthController extends Controller
{
    // Método para redireccionar a Google
    public function redirectToGoogle()
    {
        $clientId = config('services.google.client_id');
        $redirectUri = config('services.google.redirect');

        $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
            'access_type' => 'offline',
            'prompt' => 'select_account'
        ]);

        return redirect($url);
    }

    // Método para procesar la respuesta de Google
    public function handleGoogleCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect('/login')->with('error', 'Error en autenticación con Google: ' . $request->error);
        }

        $code = $request->code;

        try {
            // Obtener token de acceso
            $response = Http::post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'code' => $code,
                'redirect_uri' => config('services.google.redirect'),
                'grant_type' => 'authorization_code'
            ]);

            $data = $response->json();

            if (!isset($data['access_token'])) {
                return redirect('/login')->with('error', 'No se pudo obtener el token de Google');
            }

            // Obtener información del usuario
            $userInfo = Http::withToken($data['access_token'])
                ->get('https://www.googleapis.com/oauth2/v3/userinfo')
                ->json();

            // Buscar o crear usuario
            $usuario = Usuario::where('email', $userInfo['email'])->first();

            if (!$usuario) {
                $usuario = Usuario::create([
                    'nombre' => $userInfo['name'] ?? $userInfo['email'],
                    'email' => $userInfo['email'],
                    'password' => Hash::make(Str::random(24)),
                    'id_social' => $userInfo['sub'],
                    'tipo_social' => 'google',
                    'email_verificado_en' => now()
                ]);
            } else {
                $usuario->id_social = $userInfo['sub'];
                $usuario->tipo_social = 'google';
                if (!$usuario->email_verificado_en) {
                    $usuario->email_verificado_en = now();
                }
                $usuario->save();
            }

            // Iniciar sesión del usuario
            Auth::login($usuario, true);

            return redirect('/');

        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // Método para redireccionar a Facebook
    public function redirectToFacebook()
    {
        $clientId = config('services.facebook.client_id');
        $redirectUri = config('services.facebook.redirect');

        $url = 'https://www.facebook.com/v14.0/dialog/oauth?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'email'
        ]);

        return redirect($url);
    }

    // Método para procesar la respuesta de Facebook
    public function handleFacebookCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect('/login')->with('error', 'Error en autenticación con Facebook: ' . $request->error_description);
        }

        $code = $request->code;

        try {
            // Obtener token de acceso
            $response = Http::get('https://graph.facebook.com/v14.0/oauth/access_token', [
                'client_id' => config('services.facebook.client_id'),
                'client_secret' => config('services.facebook.client_secret'),
                'code' => $code,
                'redirect_uri' => config('services.facebook.redirect')
            ]);

            $data = $response->json();

            if (!isset($data['access_token'])) {
                return redirect('/login')->with('error', 'No se pudo obtener el token de Facebook');
            }

            // Obtener información del usuario
            $userInfo = Http::withToken($data['access_token'])
                ->get('https://graph.facebook.com/v14.0/me?fields=id,name,email')
                ->json();

            // Buscar o crear usuario
            $usuario = Usuario::where('email', $userInfo['email'])->first();

            if (!$usuario) {
                $usuario = Usuario::create([
                    'nombre' => $userInfo['name'] ?? $userInfo['email'],
                    'email' => $userInfo['email'],
                    'password' => Hash::make(Str::random(24)),
                    'id_social' => $userInfo['id'],
                    'tipo_social' => 'facebook',
                    'email_verificado_en' => now()
                ]);
            } else {
                $usuario->id_social = $userInfo['id'];
                $usuario->tipo_social = 'facebook';
                if (!$usuario->email_verificado_en) {
                    $usuario->email_verificado_en = now();
                }
                $usuario->save();
            }

            // Iniciar sesión del usuario
            Auth::login($usuario, true);

            return redirect('/');

        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
