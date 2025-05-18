<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Marca;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminMarcaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $marcas = Marca::orderBy('nombre')->paginate(20);

        return view('admin.marcas.index', compact('marcas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.marcas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:marcas,nombre',
            'descripcion' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        // Generar slug
        $slug = Str::slug($request->nombre);

        $marca = new Marca();
        $marca->nombre = $request->nombre;
        $marca->slug = $slug;
        $marca->descripcion = $request->descripcion;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('marcas', 'public');
            $marca->logo = $logoPath;
        }

        $marca->save();

        return redirect()->route('admin.marcas.index')
                       ->with('success', 'Marca creada correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $marca = Marca::findOrFail($id);

        return view('admin.marcas.edit', compact('marca'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $marca = Marca::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100|unique:marcas,nombre,' . $id,
            'descripcion' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        // Actualizar slug solo si cambió el nombre
        if ($marca->nombre != $request->nombre) {
            $marca->slug = Str::slug($request->nombre);
        }

        $marca->nombre = $request->nombre;
        $marca->descripcion = $request->descripcion;

        if ($request->hasFile('logo')) {
            // Eliminar logo anterior si existe
            if ($marca->logo) {
                Storage::disk('public')->delete($marca->logo);
            }

            $logoPath = $request->file('logo')->store('marcas', 'public');
            $marca->logo = $logoPath;
        }

        $marca->save();

        return redirect()->route('admin.marcas.index')
                       ->with('success', 'Marca actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $marca = Marca::findOrFail($id);

        // Verificar si tiene productos asociados
        if ($marca->productos()->exists()) {
            return back()->with('error', 'No se puede eliminar esta marca porque tiene productos asociados.');
        }

        // Eliminar logo si existe
        if ($marca->logo) {
            Storage::disk('public')->delete($marca->logo);
        }

        $marca->delete();

        return redirect()->route('admin.marcas.index')
                       ->with('success', 'Marca eliminada correctamente.');
    }
}
