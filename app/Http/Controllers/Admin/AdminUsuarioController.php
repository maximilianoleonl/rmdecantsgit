<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class AdminUsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Usuario::query();

        // Búsqueda
        if ($request->has('buscar') && $request->buscar) {
            $search = $request->buscar;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $usuarios = $query->orderBy('nombre')->paginate(20);

        return view('admin.usuarios.index', compact('usuarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.usuarios.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios',
            'password' => 'required|string|min:8|confirmed',
            'es_admin' => 'boolean',
        ]);

        $usuario = new Usuario();
        $usuario->nombre = $request->nombre;
        $usuario->email = $request->email;
        $usuario->password = Hash::make($request->password);
        $usuario->es_admin = $request->es_admin ?? false;
        $usuario->save();

        return redirect()->route('admin.usuarios.index')
                       ->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $usuario = Usuario::with([
            'pedidos' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'direcciones',
            'transaccionesPuntos' => function($query) {
                $query->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);

        return view('admin.usuarios.show', compact('usuario'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $usuario = Usuario::findOrFail($id);

        return view('admin.usuarios.edit', compact('usuario'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'es_admin' => 'boolean',
            'puntos' => 'nullable|integer|min:0',
        ]);

        $usuario->nombre = $request->nombre;
        $usuario->email = $request->email;

        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->password);
        }

        $usuario->es_admin = $request->es_admin ?? false;

        if ($request->filled('puntos')) {
            $puntosAnteriores = $usuario->puntos;
            $usuario->puntos = $request->puntos;

            // Registrar transacción de puntos si hay cambio
            if ($request->puntos != $puntosAnteriores) {
                $diferencia = $request->puntos - $puntosAnteriores;
                $descripcion = "Ajuste manual de puntos por administrador";

                if ($diferencia != 0) {
                    $usuario->transaccionesPuntos()->create([
                        'puntos' => $diferencia,
                        'descripcion' => $descripcion
                    ]);
                }
            }
        }

        $usuario->save();

        return redirect()->route('admin.usuarios.index')
                       ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);

        // Verificar si tiene pedidos asociados
        if ($usuario->pedidos()->exists()) {
            return back()->with('error', 'No se puede eliminar este usuario porque tiene pedidos asociados.');
        }

        // Eliminar direcciones asociadas
        $usuario->direcciones()->delete();

        // Eliminar elementos del carrito
        if ($usuario->carrito) {
            $usuario->carrito->elementos()->delete();
            $usuario->carrito->delete();
        }

        // Eliminar favoritos
        $usuario->listaDeseos()->delete();

        // Eliminar transacciones de puntos
        $usuario->transaccionesPuntos()->delete();

        // Eliminar reseñas
        $usuario->resenas()->delete();

        // Eliminar notificaciones
        $usuario->notificaciones()->delete();

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
                       ->with('success', 'Usuario eliminado correctamente.');
    }
}
