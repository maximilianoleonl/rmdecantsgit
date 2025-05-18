<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cupon;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Support\Str;

class AdminCuponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cupones = Cupon::orderBy('created_at', 'desc')->paginate(20);

        return view('admin.cupones.index', compact('cupones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $productos = Producto::orderBy('nombre')->get();
        $usuarios = Usuario::orderBy('nombre')->get();

        return view('admin.cupones.create', compact('productos', 'usuarios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'nullable|string|max:50|unique:cupones,codigo',
            'tipo' => 'required|in:porcentaje,monto_fijo',
            'valor' => 'required|numeric|min:0',
            'compra_minima' => 'nullable|numeric|min:0',
            'es_uso_unico' => 'boolean',
            'inicia_en' => 'required|date',
            'expira_en' => 'required|date|after:inicia_en',
            'productos' => 'nullable|array',
            'usuarios' => 'nullable|array',
        ]);

        // Generar código aleatorio si no se proporciona
        $codigo = $request->codigo;
        if (!$codigo) {
            $codigo = strtoupper(Str::random(8));

            // Asegurar que el código sea único
            while (Cupon::where('codigo', $codigo)->exists()) {
                $codigo = strtoupper(Str::random(8));
            }
        }

        $cupon = new Cupon();
        $cupon->codigo = $codigo;
        $cupon->tipo = $request->tipo;
        $cupon->valor = $request->valor;
        $cupon->compra_minima = $request->compra_minima;
        $cupon->es_uso_unico = $request->es_uso_unico ?? false;
        $cupon->inicia_en = $request->inicia_en;
        $cupon->expira_en = $request->expira_en;
        $cupon->save();

        // Asociar productos si se proporcionan
        if ($request->has('productos') && is_array($request->productos)) {
            $cupon->productos()->attach($request->productos);
        }

        // Asociar usuarios si se proporcionan
        if ($request->has('usuarios') && is_array($request->usuarios)) {
            $cupon->usuarios()->attach($request->usuarios);
        }

        return redirect()->route('admin.cupones.index')
                       ->with('success', 'Cupón creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $cupon = Cupon::with(['productos', 'usuarios', 'pedidos'])->findOrFail($id);

        return view('admin.cupones.show', compact('cupon'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $cupon = Cupon::with(['productos', 'usuarios'])->findOrFail($id);
        $productos = Producto::orderBy('nombre')->get();
        $usuarios = Usuario::orderBy('nombre')->get();

        return view('admin.cupones.edit', compact('cupon', 'productos', 'usuarios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $cupon = Cupon::findOrFail($id);

        $request->validate([
            'codigo' => 'required|string|max:50|unique:cupones,codigo,' . $id,
            'tipo' => 'required|in:porcentaje,monto_fijo',
            'valor' => 'required|numeric|min:0',
            'compra_minima' => 'nullable|numeric|min:0',
            'es_uso_unico' => 'boolean',
            'inicia_en' => 'required|date',
            'expira_en' => 'required|date|after:inicia_en',
            'productos' => 'nullable|array',
            'usuarios' => 'nullable|array',
        ]);

        $cupon->codigo = $request->codigo;
        $cupon->tipo = $request->tipo;
        $cupon->valor = $request->valor;
        $cupon->compra_minima = $request->compra_minima;
        $cupon->es_uso_unico = $request->es_uso_unico ?? false;
        $cupon->inicia_en = $request->inicia_en;
        $cupon->expira_en = $request->expira_en;
        $cupon->save();

        // Actualizar productos asociados
        if ($request->has('productos')) {
            $cupon->productos()->sync($request->productos);
        } else {
            $cupon->productos()->detach();
        }

        // Actualizar usuarios asociados
        if ($request->has('usuarios')) {
            $cupon->usuarios()->sync($request->usuarios);
        } else {
            $cupon->usuarios()->detach();
        }

        return redirect()->route('admin.cupones.index')
                       ->with('success', 'Cupón actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $cupon = Cupon::findOrFail($id);

        // Verificar si el cupón ha sido utilizado en pedidos
        if ($cupon->pedidos()->exists()) {
            return back()->with('error', 'No se puede eliminar este cupón porque ha sido utilizado en pedidos.');
        }

        // Eliminar relaciones
        $cupon->productos()->detach();
        $cupon->usuarios()->detach();

        $cupon->delete();

        return redirect()->route('admin.cupones.index')
                       ->with('success', 'Cupón eliminado correctamente.');
    }
}
