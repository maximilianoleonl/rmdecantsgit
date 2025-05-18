<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Marca;

class HomeController extends Controller
{
    public function index()
    {
        // Obtener productos destacados
        $productosDestacados = Producto::with(['marca', 'imagenPrincipal', 'productoPresentaciones'])
                                    ->take(8)
                                    ->get();

        // Obtener productos nuevos
        $productosNuevos = Producto::with(['marca', 'imagenPrincipal', 'productoPresentaciones'])
                                ->orderBy('created_at', 'desc')
                                ->take(8)
                                ->get();

        // Obtener marcas para mostrar en el slider o sección de marcas
        $marcas = Marca::take(10)->get();

        // Agregar un producto destacado (probablemente es lo que falta)
        $producto = null;
        if ($productosDestacados->count() > 0) {
            $producto = $productosDestacados->first();
            // Carga las imágenes para el slider
            $producto->load('imagenes');
        }

        return view('home', compact('productosDestacados', 'productosNuevos', 'marcas', 'producto'));
    }
}
