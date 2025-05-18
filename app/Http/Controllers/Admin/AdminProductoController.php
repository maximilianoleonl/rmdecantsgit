<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Marca;
use App\Models\TipoAroma;
use App\Models\TipoProducto;
use App\Models\Presentacion;
use App\Models\ProductoPresentacion;
use App\Models\ImagenProducto;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AdminProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productos = Producto::with(['marca', 'tipoAroma', 'tipoProducto', 'imagenPrincipal'])
                           ->orderBy('nombre')
                           ->paginate(20);

        return view('admin.productos.index', compact('productos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $marcas = Marca::orderBy('nombre')->get();
        $tiposAroma = TipoAroma::orderBy('nombre')->get();
        $tiposProducto = TipoProducto::orderBy('nombre')->get();

        return view('admin.productos.create', compact('marcas', 'tiposAroma', 'tiposProducto'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'marca_id' => 'required|exists:marcas,id',
            'tipo_aroma_id' => 'required|exists:tipos_aroma,id',
            'tipo_producto_id' => 'required|exists:tipos_producto,id',
            'descripcion' => 'nullable|string',
            'porcentaje_contenido' => 'required|integer|min:1|max:100',
            'imagen' => 'nullable|image|max:2048',
        ]);

        // Generar slug único
        $slug = Str::slug($request->nombre);
        $count = 1;

        while (Producto::where('slug', $slug)->exists()) {
            $slug = Str::slug($request->nombre) . '-' . $count;
            $count++;
        }

        DB::beginTransaction();

        try {
            $producto = new Producto();
            $producto->nombre = $request->nombre;
            $producto->slug = $slug;
            $producto->marca_id = $request->marca_id;
            $producto->tipo_aroma_id = $request->tipo_aroma_id;
            $producto->tipo_producto_id = $request->tipo_producto_id;
            $producto->descripcion = $request->descripcion;
            $producto->porcentaje_contenido = $request->porcentaje_contenido;
            $producto->save();

            // Guardar imagen principal si se proporciona
            if ($request->hasFile('imagen')) {
                $imagenPath = $request->file('imagen')->store('productos', 'public');

                $imagen = new ImagenProducto();
                $imagen->producto_id = $producto->id;
                $imagen->ruta_imagen = $imagenPath;
                $imagen->es_principal = true;
                $imagen->save();
            }

            DB::commit();

            return redirect()->route('admin.productos.edit', $producto->id)
                           ->with('success', 'Producto creado correctamente. Ahora puedes agregar presentaciones e imágenes adicionales.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al crear el producto: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $producto = Producto::with(['imagenes', 'productoPresentaciones.presentacion'])
                          ->findOrFail($id);
        $marcas = Marca::orderBy('nombre')->get();
        $tiposAroma = TipoAroma::orderBy('nombre')->get();
        $tiposProducto = TipoProducto::orderBy('nombre')->get();

        return view('admin.productos.edit', compact('producto', 'marcas', 'tiposAroma', 'tiposProducto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'marca_id' => 'required|exists:marcas,id',
            'tipo_aroma_id' => 'required|exists:tipos_aroma,id',
            'tipo_producto_id' => 'required|exists:tipos_producto,id',
            'descripcion' => 'nullable|string',
            'porcentaje_contenido' => 'required|integer|min:1|max:100',
        ]);

        // Actualizar slug solo si cambió el nombre
        if ($producto->nombre != $request->nombre) {
            $slug = Str::slug($request->nombre);
            $count = 1;

            while (Producto::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = Str::slug($request->nombre) . '-' . $count;
                $count++;
            }

            $producto->slug = $slug;
        }

        $producto->nombre = $request->nombre;
        $producto->marca_id = $request->marca_id;
        $producto->tipo_aroma_id = $request->tipo_aroma_id;
        $producto->tipo_producto_id = $request->tipo_producto_id;
        $producto->descripcion = $request->descripcion;
        $producto->porcentaje_contenido = $request->porcentaje_contenido;
        $producto->save();

        return redirect()->route('admin.productos.index')
                       ->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);

        // Verificar si el producto tiene pedidos asociados
        $tieneElementosPedido = $producto->productoPresentaciones()
                                        ->whereHas('elementosPedido')
                                        ->exists();

        if ($tieneElementosPedido) {
            return back()->with('error', 'No se puede eliminar este producto porque tiene pedidos asociados.');
        }

        DB::beginTransaction();

        try {
            // Eliminar imágenes del storage
            foreach ($producto->imagenes as $imagen) {
                Storage::disk('public')->delete($imagen->ruta_imagen);
            }

            // El borrado en cascada se encargará de eliminar registros relacionados
            $producto->delete();

            DB::commit();

            return redirect()->route('admin.productos.index')
                           ->with('success', 'Producto eliminado correctamente.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al eliminar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar imágenes del producto.
     */
    public function imagenes($id)
    {
        $producto = Producto::with('imagenes')->findOrFail($id);

        return view('admin.productos.imagenes', compact('producto'));
    }

    /**
     * Subir una nueva imagen.
     */
    public function subirImagen(Request $request, $id)
    {
        $request->validate([
            'imagen' => 'required|image|max:2048',
        ]);

        $producto = Producto::findOrFail($id);

        $imagenPath = $request->file('imagen')->store('productos', 'public');

        $imagen = new ImagenProducto();
        $imagen->producto_id = $producto->id;
        $imagen->ruta_imagen = $imagenPath;
        $imagen->es_principal = false;
        $imagen->save();

        return back()->with('success', 'Imagen subida correctamente.');
    }

    /**
     * Eliminar una imagen.
     */
    public function eliminarImagen($id)
    {
        $imagen = ImagenProducto::findOrFail($id);

        // No permitir eliminar si es la única imagen y es principal
        $totalImagenes = ImagenProducto::where('producto_id', $imagen->producto_id)->count();
        if ($totalImagenes == 1 && $imagen->es_principal) {
            return back()->with('error', 'No se puede eliminar la única imagen principal del producto.');
        }

        // Si la imagen a eliminar es principal, establecer otra como principal
        if ($imagen->es_principal && $totalImagenes > 1) {
            $otraImagen = ImagenProducto::where('producto_id', $imagen->producto_id)
                                      ->where('id', '!=', $id)
                                      ->first();
            $otraImagen->es_principal = true;
            $otraImagen->save();
        }

        // Eliminar imagen del storage
        Storage::disk('public')->delete($imagen->ruta_imagen);

        $imagen->delete();

        return back()->with('success', 'Imagen eliminada correctamente.');
    }

    /**
     * Establecer imagen como principal.
     */
    public function hacerImagenPrincipal($id)
    {
        $imagen = ImagenProducto::findOrFail($id);

        // Quitar el estado principal de todas las imágenes del producto
        ImagenProducto::where('producto_id', $imagen->producto_id)
                     ->update(['es_principal' => false]);

        $imagen->es_principal = true;
        $imagen->save();

        return back()->with('success', 'Imagen establecida como principal.');
    }

    /**
     * Mostrar presentaciones del producto.
     */
    public function presentaciones($id)
    {
        $producto = Producto::with('productoPresentaciones.presentacion')->findOrFail($id);
        $presentaciones = Presentacion::orderBy('volumen')->get();

        return view('admin.productos.presentaciones', compact('producto', 'presentaciones'));
    }

    /**
     * Guardar nueva presentación para el producto.
     */
    public function guardarPresentacion(Request $request, $id)
    {
        $request->validate([
            'presentacion_id' => 'required|exists:presentaciones,id',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $producto = Producto::findOrFail($id);

        // Verificar si ya existe esta presentación para el producto
        $existente = ProductoPresentacion::where('producto_id', $id)
                                       ->where('presentacion_id', $request->presentacion_id)
                                       ->first();

        if ($existente) {
            return back()->with('error', 'Esta presentación ya existe para el producto.');
        }

        $productoPresentacion = new ProductoPresentacion();
        $productoPresentacion->producto_id = $id;
        $productoPresentacion->presentacion_id = $request->presentacion_id;
        $productoPresentacion->precio = $request->precio;
        $productoPresentacion->stock = $request->stock;
        $productoPresentacion->save();

        return back()->with('success', 'Presentación agregada correctamente.');
    }

    /**
     * Actualizar presentación existente.
     */
    public function actualizarPresentacion(Request $request, $id)
    {
        $request->validate([
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $productoPresentacion = ProductoPresentacion::findOrFail($id);
        $productoPresentacion->precio = $request->precio;
        $productoPresentacion->stock = $request->stock;
        $productoPresentacion->save();

        return back()->with('success', 'Presentación actualizada correctamente.');
    }

    /**
     * Eliminar presentación.
     */
    public function eliminarPresentacion($id)
    {
        $productoPresentacion = ProductoPresentacion::findOrFail($id);

        // Verificar si tiene elementos de pedido o carrito asociados
        if ($productoPresentacion->elementosPedido()->exists() || $productoPresentacion->elementosCarrito()->exists()) {
            return back()->with('error', 'No se puede eliminar esta presentación porque tiene pedidos o elementos de carrito asociados.');
        }

        $productoPresentacion->delete();

        return back()->with('success', 'Presentación eliminada correctamente.');
    }

    


}
