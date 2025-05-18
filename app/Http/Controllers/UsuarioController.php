<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\TransaccionPunto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function cuenta()
    {
        $usuario = Auth::user();

        return view('usuario.cuenta', compact('usuario'));
    }

    public function actualizar(Request $request)
    {
        $usuario = Auth::user();

        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,email,' . $usuario->id,
        ]);

        $usuario->nombre = $request->nombre;
        $usuario->email = $request->email;
        $usuario->save();

        return redirect()->route('usuario.cuenta')->with('success', 'Perfil actualizado correctamente.');
    }

    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'password_actual' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $usuario = Auth::user();

        if (!Hash::check($request->password_actual, $usuario->password)) {
            return back()->with('error', 'La contraseña actual no es correcta.');
        }

        $usuario->password = Hash::make($request->password);
        $usuario->save();

        return redirect()->route('usuario.cuenta')->with('success', 'Contraseña actualizada correctamente.');
    }

    public function puntos()
    {
        $usuario = Auth::user();
        $transacciones = TransaccionPunto::where('usuario_id', $usuario->id)
                                       ->orderBy('created_at', 'desc')
                                       ->paginate(15);

        return view('usuario.puntos', compact('usuario', 'transacciones'));
    }
}
