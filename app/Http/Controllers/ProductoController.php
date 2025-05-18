<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Resena;

class ProductoController extends Controller
{

    public function show($slug)
    {
        $producto = Producto::with([
            'marca',
            'tipoAroma',
            'tipoProducto',
            'imagenes',
            'productoPresentaciones.presentacion',
            'resenas' => function($query) {
                $query->where('esta_aprobada', true)
                      ->orderBy('created_at', 'desc');
            },
            'resenas.usuario'
        ])->where('slug', $slug)->firstOrFail();

        // Productos relacionados (misma marca o mismo tipo de aroma)
        $productosRelacionados = Producto::with(['marca', 'imagenPrincipal'])
                                      ->where('id', '!=', $producto->id)
                                      ->where(function($query) use ($producto) {
                                          $query->where('marca_id', $producto->marca_id)
                                                ->orWhere('tipo_aroma_id', $producto->tipo_aroma_id);
                                      })
                                      ->take(4)
                                      ->get();

        // Calcular promedio de calificaciones
        $calificacionPromedio = $producto->resenas->avg('calificacion');

        return view('producto.detalle', compact('producto', 'productosRelacionados', 'calificacionPromedio'));
    }
/**
 * Obtener las imágenes de un producto para un slider en formato JSON
 *
 * @param int $id ID del producto
 * @return \Illuminate\Http\JsonResponse
 */
public function imagenesProductoJson($id)
{
    $producto = Producto::with(['imagenes' => function($query) {
        $query->orderByRaw('es_principal DESC'); // Poner la imagen principal primero
    }])->findOrFail($id);

    $imagenes = $producto->imagenes->map(function($imagen) {
        return [
            'id' => $imagen->id,
            'url' => asset('storage/' . $imagen->ruta_imagen),
            'es_principal' => $imagen->es_principal,
            'alt' => $producto->nombre . ($imagen->es_principal ? ' - Imagen Principal' : '')
        ];
    });

    return response()->json($imagenes);
}

}
