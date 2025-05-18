<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ListaDeseo;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;

class ListaDeseoController extends Controller
{
    public function index()
    {
        $favoritos = ListaDeseo::with(['producto.marca', 'producto.imagenPrincipal', 'producto.productoPresentaciones.presentacion'])
                             ->where('usuario_id', Auth::id())
                             ->paginate(12);

        return view('favoritos.index', compact('favoritos'));
    }

    public function agregar($productoId)
    {
        $producto = Producto::findOrFail($productoId);

        $yaExiste = ListaDeseo::where('usuario_id', Auth::id())
                             ->where('producto_id', $productoId)
                             ->exists();

        if ($yaExiste) {
            return back()->with('info', 'Este producto ya está en tu lista de favoritos.');
        }

        $listaDeseo = new ListaDeseo();
        $listaDeseo->usuario_id = Auth::id();
        $listaDeseo->producto_id = $productoId;
        $listaDeseo->save();

        return back()->with('success', 'Producto agregado a favoritos.');
    }

    public function eliminar($productoId)
    {
        ListaDeseo::where('usuario_id', Auth::id())
                 ->where('producto_id', $productoId)
                 ->delete();

        return back()->with('success', 'Producto eliminado de favoritos.');
    }
}
