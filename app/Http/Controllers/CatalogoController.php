<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Marca;
use App\Models\TipoAroma;
use App\Models\TipoProducto;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        // Construir la consulta base
        $query = Producto::with(['marca', 'tipoAroma', 'tipoProducto', 'imagenPrincipal', 'productoPresentaciones']);

        // Aplicar filtros si existen
        if ($request->has('precio_min') && $request->precio_min > 0) {
            $query->whereHas('productoPresentaciones', function($q) use ($request) {
                $q->where('precio', '>=', $request->precio_min);
            });
        }

        if ($request->has('precio_max') && $request->precio_max > 0) {
            $query->whereHas('productoPresentaciones', function($q) use ($request) {
                $q->where('precio', '<=', $request->precio_max);
            });
        }

        if ($request->has('marca_id') && $request->marca_id > 0) {
            $query->where('marca_id', $request->marca_id);
        }

        if ($request->has('tipo_aroma_id') && $request->tipo_aroma_id > 0) {
            $query->where('tipo_aroma_id', $request->tipo_aroma_id);
        }

        if ($request->has('tipo_producto_id') && $request->tipo_producto_id > 0) {
            $query->where('tipo_producto_id', $request->tipo_producto_id);
        }

        // Ordenar resultados
        if ($request->has('ordenar')) {
            switch ($request->ordenar) {
                case 'precio_asc':
                    $query->join('producto_presentaciones', 'productos.id', '=', 'producto_presentaciones.producto_id')
                          ->orderBy('producto_presentaciones.precio', 'asc')
                          ->select('productos.*')
                          ->distinct();
                    break;
                case 'precio_desc':
                    $query->join('producto_presentaciones', 'productos.id', '=', 'producto_presentaciones.producto_id')
                          ->orderBy('producto_presentaciones.precio', 'desc')
                          ->select('productos.*')
                          ->distinct();
                    break;
                case 'nombre_asc':
                    $query->orderBy('nombre', 'asc');
                    break;
                case 'nombre_desc':
                    $query->orderBy('nombre', 'desc');
                    break;
                case 'recientes':
                    $query->orderBy('created_at', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Paginar resultados
        $productos = $query->paginate(12);

        // Obtener datos para filtros
        $marcas = Marca::orderBy('nombre')->get();
        $tiposAroma = TipoAroma::orderBy('nombre')->get();
        $tiposProducto = TipoProducto::orderBy('nombre')->get();

        return view('catalogo.index', compact('productos', 'marcas', 'tiposAroma', 'tiposProducto'));
    }

    public function porMarca($slug)
    {
        $marca = Marca::where('slug', $slug)->firstOrFail();

        $productos = Producto::with(['marca', 'tipoAroma', 'tipoProducto', 'imagenPrincipal', 'productoPresentaciones'])
                           ->where('marca_id', $marca->id)
                           ->paginate(12);

        // Datos para filtros
        $marcas = Marca::orderBy('nombre')->get();
        $tiposAroma = TipoAroma::orderBy('nombre')->get();
        $tiposProducto = TipoProducto::orderBy('nombre')->get();

        return view('catalogo.marca', compact('productos', 'marca', 'marcas', 'tiposAroma', 'tiposProducto'));
    }

    public function porAroma($slug)
    {
        $tipoAroma = TipoAroma::where('slug', $slug)->firstOrFail();

        $productos = Producto::with(['marca', 'tipoAroma', 'tipoProducto', 'imagenPrincipal', 'productoPresentaciones'])
                           ->where('tipo_aroma_id', $tipoAroma->id)
                           ->paginate(12);

        // Datos para filtros
        $marcas = Marca::orderBy('nombre')->get();
        $tiposAroma = TipoAroma::orderBy('nombre')->get();
        $tiposProducto = TipoProducto::orderBy('nombre')->get();

        return view('catalogo.aroma', compact('productos', 'tipoAroma', 'marcas', 'tiposAroma', 'tiposProducto'));
    }

    public function porTipo($slug)
    {
        $tipoProducto = TipoProducto::where('slug', $slug)->firstOrFail();

        $productos = Producto::with(['marca', 'tipoAroma', 'tipoProducto', 'imagenPrincipal', 'productoPresentaciones'])
                           ->where('tipo_producto_id', $tipoProducto->id)
                           ->paginate(12);

        // Datos para filtros
        $marcas = Marca::orderBy('nombre')->get();
        $tiposAroma = TipoAroma::orderBy('nombre')->get();
        $tiposProducto = TipoProducto::orderBy('nombre')->get();

        return view('catalogo.tipo', compact('productos', 'tipoProducto', 'marcas', 'tiposAroma', 'tiposProducto'));
    }

    public function buscar(Request $request)
    {
        $busqueda = $request->input('q');

        $productos = Producto::with(['marca', 'tipoAroma', 'tipoProducto', 'imagenPrincipal', 'productoPresentaciones'])
                           ->where('nombre', 'LIKE', "%{$busqueda}%")
                           ->orWhereHas('marca', function($query) use ($busqueda) {
                               $query->where('nombre', 'LIKE', "%{$busqueda}%");
                           })
                           ->paginate(12);

        return view('catalogo.busqueda', compact('productos', 'busqueda'));
    }
}
