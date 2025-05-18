<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Usuario;
use App\Models\Producto;
use App\Models\Resena;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        // Estadísticas rápidas para el dashboard
        $totalVentas = Pedido::where('estado', '!=', 'cancelado')->sum('total');
        $ventasHoy = Pedido::whereDate('created_at', Carbon::today())->sum('total');
        $totalUsuarios = Usuario::count();
        $totalProductos = Producto::count();

        // Pedidos recientes
        $pedidosRecientes = Pedido::with(['usuario'])
                                 ->orderBy('created_at', 'desc')
                                 ->take(10)
                                 ->get();

        // Productos más vendidos
        $productosPopulares = DB::table('elementos_pedido')
            ->join('producto_presentaciones', 'elementos_pedido.producto_presentacion_id', '=', 'producto_presentaciones.id')
            ->join('productos', 'producto_presentaciones.producto_id', '=', 'productos.id')
            ->join('pedidos', 'elementos_pedido.pedido_id', '=', 'pedidos.id')
            ->select('productos.id', 'productos.nombre', DB::raw('SUM(elementos_pedido.cantidad) as total_vendido'))
            ->where('pedidos.estado', '!=', 'cancelado')
            ->groupBy('productos.id', 'productos.nombre')
            ->orderBy('total_vendido', 'desc')
            ->take(5)
            ->get();

        return view('admin.index', compact(
            'totalVentas',
            'ventasHoy',
            'totalUsuarios',
            'totalProductos',
            'pedidosRecientes',
            'productosPopulares'
        ));
    }

    public function resenas()
    {
        $resenas = Resena::with(['producto', 'usuario'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(20);

        return view('admin.resenas.index', compact('resenas'));
    }

    public function aprobarResena($id)
    {
        $resena = Resena::findOrFail($id);
        $resena->esta_aprobada = true;
        $resena->save();

        return back()->with('success', 'Reseña aprobada correctamente.');
    }

    public function rechazarResena($id)
    {
        $resena = Resena::findOrFail($id);
        $resena->esta_aprobada = false;
        $resena->save();

        return back()->with('success', 'Reseña rechazada correctamente.');
    }

    public function eliminarResena($id)
    {
        $resena = Resena::findOrFail($id);
        $resena->delete();

        return back()->with('success', 'Reseña eliminada correctamente.');
    }

    public function estadisticas()
    {
        return view('admin.estadisticas.index');
    }

    public function estadisticasVentas()
    {
        // Ventas por mes (últimos 12 meses)
        $ventasPorMes = DB::table('pedidos')
            ->select(DB::raw('YEAR(created_at) as año, MONTH(created_at) as mes, SUM(total) as total_ventas'))
            ->where('estado', '!=', 'cancelado')
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('año', 'mes')
            ->orderBy('año')
            ->orderBy('mes')
            ->get();

        // Ventas por método de pago
        $ventasPorMetodoPago = DB::table('pedidos')
            ->join('metodos_pago', 'pedidos.metodo_pago_id', '=', 'metodos_pago.id')
            ->select('metodos_pago.nombre', DB::raw('COUNT(pedidos.id) as total_pedidos, SUM(pedidos.total) as total_ventas'))
            ->where('pedidos.estado', '!=', 'cancelado')
            ->groupBy('metodos_pago.nombre')
            ->get();

        return view('admin.estadisticas.ventas', compact('ventasPorMes', 'ventasPorMetodoPago'));
    }

    public function estadisticasProductos()
    {
        // Productos más vendidos
        $productosMasVendidos = DB::table('elementos_pedido')
            ->join('producto_presentaciones', 'elementos_pedido.producto_presentacion_id', '=', 'producto_presentaciones.id')
            ->join('productos', 'producto_presentaciones.producto_id', '=', 'productos.id')
            ->join('pedidos', 'elementos_pedido.pedido_id', '=', 'pedidos.id')
            ->select('productos.id', 'productos.nombre', DB::raw('SUM(elementos_pedido.cantidad) as total_vendido, SUM(elementos_pedido.cantidad * elementos_pedido.precio) as total_ingresos'))
            ->where('pedidos.estado', '!=', 'cancelado')
            ->groupBy('productos.id', 'productos.nombre')
            ->orderBy('total_vendido', 'desc')
            ->take(20)
            ->get();

        // Productos por categoría
        $productosPorCategoria = DB::table('productos')
            ->join('tipos_producto', 'productos.tipo_producto_id', '=', 'tipos_producto.id')
            ->select('tipos_producto.nombre', DB::raw('COUNT(productos.id) as total_productos'))
            ->groupBy('tipos_producto.nombre')
            ->get();

        return view('admin.estadisticas.productos', compact('productosMasVendidos', 'productosPorCategoria'));
    }

    public function estadisticasUsuarios()
    {
        // Usuarios con más compras
        $usuariosConMasCompras = DB::table('pedidos')
            ->join('usuarios', 'pedidos.usuario_id', '=', 'usuarios.id')
            ->select('usuarios.id', 'usuarios.nombre', 'usuarios.email', DB::raw('COUNT(pedidos.id) as total_pedidos, SUM(pedidos.total) as total_gastado'))
            ->where('pedidos.estado', '!=', 'cancelado')
            ->groupBy('usuarios.id', 'usuarios.nombre', 'usuarios.email')
            ->orderBy('total_gastado', 'desc')
            ->take(20)
            ->get();

        // Nuevos usuarios por mes
        $nuevosPorMes = DB::table('usuarios')
            ->select(DB::raw('YEAR(created_at) as año, MONTH(created_at) as mes, COUNT(id) as total_usuarios'))
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('año', 'mes')
            ->orderBy('año')
            ->orderBy('mes')
            ->get();

        return view('admin.estadisticas.usuarios', compact('usuariosConMasCompras', 'nuevosPorMes'));
    }
}
