<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;

class AdminPedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Pedido::with(['usuario', 'direccion']);

        // Filtrar por estado
        if ($request->has('estado') && $request->estado) {
            $query->where('estado', $request->estado);
        }

        // Filtrar por fecha
        if ($request->has('fecha_inicio') && $request->fecha_inicio) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin') && $request->fecha_fin) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }

        // Ordenar
        $query->orderBy('created_at', 'desc');

        $pedidos = $query->paginate(20);

        return view('admin.pedidos.index', compact('pedidos'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $pedido = Pedido::with([
            'usuario',
            'direccion',
            'metodoPago',
            'elementos.productoPresentacion.producto.imagenPrincipal',
            'elementos.productoPresentacion.presentacion'
        ])->findOrFail($id);

        return view('admin.pedidos.show', compact('pedido'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $pedido = Pedido::with(['usuario', 'direccion', 'metodoPago'])->findOrFail($id);

        return view('admin.pedidos.edit', compact('pedido'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        $request->validate([
            'estado' => 'required|in:pendiente,procesando,enviado,entregado,cancelado',
            'numero_seguimiento' => 'nullable|string|max:100',
            'notas' => 'nullable|string',
        ]);

        $pedido->estado = $request->estado;
        $pedido->numero_seguimiento = $request->numero_seguimiento;
        $pedido->notas = $request->notas;
        $pedido->save();

        return redirect()->route('admin.pedidos.show', $pedido->id)
                       ->with('success', 'Pedido actualizado correctamente.');
    }

    /**
     * Cambiar el estado de un pedido.
     */
    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,procesando,enviado,entregado,cancelado',
        ]);

        $pedido = Pedido::findOrFail($id);
        $pedido->estado = $request->estado;
        $pedido->save();

        return back()->with('success', 'Estado del pedido actualizado correctamente.');
    }

    /**
     * Actualizar número de seguimiento.
     */
    public function actualizarNumeroSeguimiento(Request $request, $id)
    {
        $request->validate([
            'numero_seguimiento' => 'required|string|max:100',
        ]);

        $pedido = Pedido::findOrFail($id);
        $pedido->numero_seguimiento = $request->numero_seguimiento;
        $pedido->save();

        return back()->with('success', 'Número de seguimiento actualizado correctamente.');
    }
}
