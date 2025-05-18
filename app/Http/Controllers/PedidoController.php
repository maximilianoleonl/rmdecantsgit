<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use Illuminate\Support\Facades\Auth;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::where('usuario_id', Auth::id())
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);

        return view('pedidos.index', compact('pedidos'));
    }

    public function show($id)
    {
        $pedido = Pedido::with([
                'elementos.productoPresentacion.producto.imagenPrincipal',
                'elementos.productoPresentacion.presentacion',
                'direccion',
                'metodoPago'
            ])
            ->where('usuario_id', Auth::id())
            ->findOrFail($id);

        return view('pedidos.show', compact('pedido'));
    }
}
