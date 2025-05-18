<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Direccion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DireccionController extends Controller
{
    public function index()
    {
        $direcciones = Direccion::where('usuario_id', Auth::id())->get();

        return view('direcciones.index', compact('direcciones'));
    }

    public function create()
    {
        return view('direcciones.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'calle' => 'required|string|max:255',
            'numero' => 'required|string|max:20',
            'colonia' => 'required|string|max:100',
            'ciudad' => 'required|string|max:100',
            'estado' => 'required|string|max:100',
            'pais' => 'required|string|max:100',
            'codigo_postal' => 'required|string|max:20',
            'telefono' => 'required|string|max:20',
            'es_predeterminada' => 'boolean'
        ]);

        DB::beginTransaction();

        try {
            // Si la dirección es predeterminada, quitar esa marca de las demás
            if ($request->es_predeterminada) {
                Direccion::where('usuario_id', Auth::id())
                       ->update(['es_predeterminada' => false]);
            }

            $direccion = new Direccion();
            $direccion->usuario_id = Auth::id();
            $direccion->calle = $request->calle;
            $direccion->numero = $request->numero;
            $direccion->numero_interior = $request->numero_interior;
            $direccion->colonia = $request->colonia;
            $direccion->ciudad = $request->ciudad;
            $direccion->estado = $request->estado;
            $direccion->pais = $request->pais;
            $direccion->codigo_postal = $request->codigo_postal;
            $direccion->telefono = $request->telefono;
            $direccion->es_predeterminada = $request->es_predeterminada ?? false;
            $direccion->save();

            DB::commit();

            return redirect()->route('direcciones.index')->with('success', 'Dirección guardada correctamente.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Ocurrió un error al guardar la dirección: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $direccion = Direccion::where('usuario_id', Auth::id())->findOrFail($id);

        return view('direcciones.edit', compact('direccion'));
    }

    public function update(Request $request, $id)
    {
        $direccion = Direccion::where('usuario_id', Auth::id())->findOrFail($id);

        $request->validate([
            'calle' => 'required|string|max:255',
            'numero' => 'required|string|max:20',
            'colonia' => 'required|string|max:100',
            'ciudad' => 'required|string|max:100',
            'estado' => 'required|string|max:100',
            'pais' => 'required|string|max:100',
            'codigo_postal' => 'required|string|max:20',
            'telefono' => 'required|string|max:20',
            'es_predeterminada' => 'boolean'
        ]);

        DB::beginTransaction();

        try {
            // Si la dirección es predeterminada, quitar esa marca de las demás
            if ($request->es_predeterminada) {
                Direccion::where('usuario_id', Auth::id())
                       ->where('id', '!=', $id)
                       ->update(['es_predeterminada' => false]);
            }

            $direccion->calle = $request->calle;
            $direccion->numero = $request->numero;
            $direccion->numero_interior = $request->numero_interior;
            $direccion->colonia = $request->colonia;
            $direccion->ciudad = $request->ciudad;
            $direccion->estado = $request->estado;
            $direccion->pais = $request->pais;
            $direccion->codigo_postal = $request->codigo_postal;
            $direccion->telefono = $request->telefono;
            $direccion->es_predeterminada = $request->es_predeterminada ?? false;
            $direccion->save();

            DB::commit();

            return redirect()->route('direcciones.index')->with('success', 'Dirección actualizada correctamente.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Ocurrió un error al actualizar la dirección: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $direccion = Direccion::where('usuario_id', Auth::id())->findOrFail($id);

        // Verificar si la dirección está asociada a algún pedido
        if ($direccion->pedidos()->exists()) {
            return back()->with('error', 'No se puede eliminar esta dirección porque está asociada a uno o más pedidos.');
        }

        $direccion->delete();

        return redirect()->route('direcciones.index')->with('success', 'Dirección eliminada correctamente.');
    }

    public function hacerPredeterminada($id)
    {
        DB::beginTransaction();

        try {
            // Quitar la marca de predeterminada de todas las direcciones del usuario
            Direccion::where('usuario_id', Auth::id())
                   ->update(['es_predeterminada' => false]);

            // Marcar la dirección seleccionada como predeterminada
            $direccion = Direccion::where('usuario_id', Auth::id())->findOrFail($id);
            $direccion->es_predeterminada = true;
            $direccion->save();

            DB::commit();

            return redirect()->route('direcciones.index')->with('success', 'Dirección establecida como predeterminada.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }
}
