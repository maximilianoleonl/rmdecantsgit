<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resena;
use App\Models\Producto;
use App\Models\Pedido;
use Illuminate\Support\Facades\Auth;

class ResenaController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'calificacion' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string'
        ]);

        $producto = Producto::findOrFail($id);

        // Verificar si el usuario ha comprado el producto
        $haComprado = Pedido::where('usuario_id', Auth::id())
                          ->whereHas('elementos.productoPresentacion', function($query) use ($id) {
                              $query->where('producto_id', $id);
                          })
                          ->exists();

        if (!$haComprado) {
            return back()->with('error', 'Solo puedes dejar reseñas de productos que hayas comprado.');
        }

        // Verificar si ya existe una reseña del usuario para este producto
        $resenaExistente = Resena::where('usuario_id', Auth::id())
                                ->where('producto_id', $id)
                                ->first();

        if ($resenaExistente) {
            $resenaExistente->calificacion = $request->calificacion;
            $resenaExistente->comentario = $request->comentario;
            $resenaExistente->save();

            return back()->with('success', 'Tu reseña ha sido actualizada correctamente.');
        } else {
            $resena = new Resena();
            $resena->usuario_id = Auth::id();
            $resena->producto_id = $id;
            $resena->calificacion = $request->calificacion;
            $resena->comentario = $request->comentario;
            $resena->esta_aprobada = true; // Se podría cambiar si se quiere moderar las reseñas
            $resena->save();

            return back()->with('success', 'Tu reseña ha sido publicada correctamente.');
        }
    }

    public function misResenas()
    {
        $resenas = Resena::with(['producto.imagenPrincipal'])
                        ->where('usuario_id', Auth::id())
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);

        return view('resenas.mis-resenas', compact('resenas'));
    }
}
