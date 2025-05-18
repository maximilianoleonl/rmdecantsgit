<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Carrito;
use App\Models\Direccion;
use App\Models\MetodoPago;
use App\Models\Pedido;
use App\Models\ElementoPedido;
use App\Models\ProductoPresentacion;
use App\Models\TransaccionPunto;
use App\Models\Cupon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        // Verificar si el carrito está vacío
        $carrito = Carrito::where('usuario_id', Auth::id())
                         ->with(['elementos.productoPresentacion.producto', 'elementos.productoPresentacion.presentacion'])
                         ->first();

        if (!$carrito || $carrito->elementos->isEmpty()) {
            return redirect()->route('carrito.index')->with('error', 'Tu carrito está vacío. Agrega productos antes de proceder al pago.');
        }

        // Obtener direcciones del usuario
        $direcciones = Direccion::where('usuario_id', Auth::id())->get();

        // Obtener métodos de pago disponibles
        $metodosPago = MetodoPago::where('esta_activo', true)->get();

        return view('checkout.index', compact('carrito', 'direcciones', 'metodosPago'));
    }

    public function procesar(Request $request)
    {
        $request->validate([
            'direccion_id' => 'required|exists:direcciones,id',
            'metodo_pago_id' => 'required|exists:metodos_pago,id'
        ]);

        // Verificar que la dirección pertenezca al usuario
        $direccion = Direccion::where('id', $request->direccion_id)
                           ->where('usuario_id', Auth::id())
                           ->firstOrFail();

        // Obtener carrito y elementos
        $carrito = Carrito::where('usuario_id', Auth::id())
                         ->with(['elementos.productoPresentacion.producto', 'elementos.productoPresentacion.presentacion'])
                         ->firstOrFail();

        // Verificar stock antes de procesar el pedido
        foreach ($carrito->elementos as $elemento) {
            if ($elemento->productoPresentacion->stock < $elemento->cantidad) {
                return redirect()->route('carrito.index')->with('error', "No hay suficiente stock para {$elemento->productoPresentacion->producto->nombre}");
            }
        }

        // Calcular subtotal, descuento y costo de envío
        $subtotal = $carrito->subtotal;
        $descuento = 0;

        // Aplicar cupón si existe en la sesión
        $cupon = null;
        if (session()->has('cupon_id')) {
            $cupon = Cupon::find(session('cupon_id'));
            if ($cupon && $cupon->esta_activo) {
                if ($cupon->tipo == 'porcentaje') {
                    $descuento = $subtotal * ($cupon->valor / 100);
                } else {
                    $descuento = $cupon->valor;
                }
            }
        }

        // Calcular costo de envío
        $costoEnvio = ($subtotal - $descuento) >= 2000 ? 0 : 150;

        // Calcular total
        $total = $subtotal - $descuento + $costoEnvio;

        // Calcular puntos a ganar (1 punto por cada $10 MXN)
        $puntosGanados = floor($total / 10);

        // Crear pedido
        DB::beginTransaction();

        try {
            $pedido = new Pedido();
            $pedido->usuario_id = Auth::id();
            $pedido->direccion_id = $direccion->id;
            $pedido->metodo_pago_id = $request->metodo_pago_id;
            $pedido->subtotal = $subtotal;
            $pedido->descuento = $descuento;
            $pedido->costo_envio = $costoEnvio;
            $pedido->total = $total;
            $pedido->cupon_id = $cupon ? $cupon->id : null;
            $pedido->estado = 'pendiente';
            $pedido->puntos_ganados = $puntosGanados;
            $pedido->save();

            // Crear elementos del pedido
            foreach ($carrito->elementos as $elemento) {
                $elementoPedido = new ElementoPedido();
                $elementoPedido->pedido_id = $pedido->id;
                $elementoPedido->producto_presentacion_id = $elemento->producto_presentacion_id;
                $elementoPedido->precio = $elemento->productoPresentacion->precio;
                $elementoPedido->cantidad = $elemento->cantidad;
                $elementoPedido->save();

                // Actualizar stock
                $productoPresentacion = $elemento->productoPresentacion;
                $productoPresentacion->stock -= $elemento->cantidad;
                $productoPresentacion->save();
            }

            // Limpiar carrito
            $carrito->elementos()->delete();

            // Limpiar cupón de la sesión
            session()->forget('cupon_id');

            DB::commit();

            return redirect()->route('checkout.confirmar', ['id' => $pedido->id]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('checkout.index')->with('error', 'Ocurrió un error al procesar el pedido: ' . $e->getMessage());
        }
    }

    public function confirmar(Request $request)
    {
        $pedido = Pedido::with([
                'usuario',
                'direccion',
                'metodoPago',
                'elementos.productoPresentacion.producto.imagenPrincipal',
                'elementos.productoPresentacion.presentacion'
            ])
            ->where('usuario_id', Auth::id())
            ->findOrFail($request->id);

        return view('checkout.confirmar', compact('pedido'));
    }

    public function procesarPago(Request $request)
    {
        $pedido = Pedido::where('usuario_id', Auth::id())->findOrFail($request->pedido_id);

        // Aquí se integraría con la pasarela de pago (Stripe, PayPal, etc.)
        // Por simplicidad, marcamos el pedido como pagado

        $pedido->pagado = true;
        $pedido->id_pago = 'PAGO-' . time(); // Simulación de ID de pago
        $pedido->save();

        // Registrar los puntos ganados
        $usuario = Auth::user();
        $usuario->puntos += $pedido->puntos_ganados;
        $usuario->save();

        TransaccionPunto::create([
            'usuario_id' => $usuario->id,
            'pedido_id' => $pedido->id,
            'puntos' => $pedido->puntos_ganados,
            'descripcion' => "Puntos ganados por compra #{$pedido->id}"
        ]);

        return redirect()->route('checkout.exito', ['id' => $pedido->id]);
    }

    public function exito($id)
    {
        $pedido = Pedido::with([
                'usuario',
                'direccion',
                'metodoPago',
                'elementos.productoPresentacion.producto'
            ])
            ->where('usuario_id', Auth::id())
            ->findOrFail($id);

        return view('checkout.exito', compact('pedido'));
    }
}
