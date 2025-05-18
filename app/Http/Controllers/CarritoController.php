<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Carrito;
use App\Models\ElementoCarrito;
use App\Models\ProductoPresentacion;
use App\Models\Cupon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CarritoController extends Controller
{
    public function index()
    {
        // Si el usuario está autenticado, obtenemos su carrito
        if (Auth::check()) {
            $carrito = Carrito::where('usuario_id', Auth::id())
                            ->with(['elementos.productoPresentacion.producto.imagenPrincipal',
                                   'elementos.productoPresentacion.presentacion'])
                            ->first();

            if (!$carrito) {
                $carrito = new Carrito();
                $carrito->usuario_id = Auth::id();
                $carrito->save();
            }
        } else {
            // Para usuarios no autenticados, usamos la sesión
            $elementos = Session::get('carrito', []);
            $productoPresentacionIds = array_keys($elementos);

            $elementosCarrito = [];
            $subtotal = 0;

            if (count($productoPresentacionIds) > 0) {
                $productoPresentaciones = ProductoPresentacion::with(['producto.imagenPrincipal', 'presentacion'])
                                                         ->whereIn('id', $productoPresentacionIds)
                                                         ->get();

                foreach ($productoPresentaciones as $pp) {
                    $cantidad = $elementos[$pp->id];
                    $elementosCarrito[] = [
                        'id' => $pp->id,
                        'producto' => $pp->producto,
                        'presentacion' => $pp->presentacion,
                        'precio' => $pp->precio,
                        'cantidad' => $cantidad,
                        'subtotal' => $pp->precio * $cantidad
                    ];
                    $subtotal += $pp->precio * $cantidad;
                }
            }

            // Calcular costo de envío y total
            $costoEnvio = $subtotal >= 2000 ? 0 : 150; // Envío gratis si es mayor a $2000 MXN
            $total = $subtotal + $costoEnvio;

            return view('carrito.index', compact('elementosCarrito', 'subtotal', 'costoEnvio', 'total'));
        }

        return view('carrito.index', compact('carrito'));
    }

    public function agregar(Request $request)
    {
        $request->validate([
            'producto_presentacion_id' => 'required|exists:producto_presentaciones,id',
            'cantidad' => 'required|integer|min:1'
        ]);

        $productoPresentacionId = $request->producto_presentacion_id;
        $cantidad = $request->cantidad;

        // Verificar stock disponible
        $productoPresentacion = ProductoPresentacion::findOrFail($productoPresentacionId);
        if ($productoPresentacion->stock < $cantidad) {
            return redirect()->back()->with('error', 'No hay suficiente stock disponible.');
        }

        if (Auth::check()) {
            // Usuario autenticado: guardar en BD
            $carrito = Carrito::firstOrCreate(['usuario_id' => Auth::id()]);

            $elementoExistente = ElementoCarrito::where('carrito_id', $carrito->id)
                                             ->where('producto_presentacion_id', $productoPresentacionId)
                                             ->first();

            if ($elementoExistente) {
                $elementoExistente->cantidad += $cantidad;
                $elementoExistente->save();
            } else {
                ElementoCarrito::create([
                    'carrito_id' => $carrito->id,
                    'producto_presentacion_id' => $productoPresentacionId,
                    'cantidad' => $cantidad
                ]);
            }
        } else {
            // Usuario no autenticado: guardar en sesión
            $carrito = Session::get('carrito', []);

            if (isset($carrito[$productoPresentacionId])) {
                $carrito[$productoPresentacionId] += $cantidad;
            } else {
                $carrito[$productoPresentacionId] = $cantidad;
            }

            Session::put('carrito', $carrito);
        }

        return redirect()->route('carrito.index')->with('success', 'Producto agregado al carrito correctamente.');
    }

    public function actualizar(Request $request)
    {
        $request->validate([
            'elementos' => 'required|array',
            'elementos.*.id' => 'required',
            'elementos.*.cantidad' => 'required|integer|min:1'
        ]);

        if (Auth::check()) {
            // Usuario autenticado: actualizar en BD
            foreach ($request->elementos as $elemento) {
                $elementoCarrito = ElementoCarrito::findOrFail($elemento['id']);

                // Verificar que el elemento pertenezca al carrito del usuario
                $carrito = Carrito::where('usuario_id', Auth::id())->firstOrFail();
                if ($elementoCarrito->carrito_id != $carrito->id) {
                    continue;
                }

                // Verificar stock disponible
                $productoPresentacion = $elementoCarrito->productoPresentacion;
                if ($productoPresentacion->stock < $elemento['cantidad']) {
                    return redirect()->back()->with('error', "No hay suficiente stock para {$productoPresentacion->producto->nombre}");
                }

                $elementoCarrito->cantidad = $elemento['cantidad'];
                $elementoCarrito->save();
            }
        } else {
            // Usuario no autenticado: actualizar en sesión
            $carrito = Session::get('carrito', []);

            foreach ($request->elementos as $elemento) {
                if (isset($carrito[$elemento['id']])) {
                    // Verificar stock disponible
                    $productoPresentacion = ProductoPresentacion::find($elemento['id']);
                    if ($productoPresentacion && $productoPresentacion->stock < $elemento['cantidad']) {
                        return redirect()->back()->with('error', "No hay suficiente stock para {$productoPresentacion->producto->nombre}");
                    }

                    $carrito[$elemento['id']] = $elemento['cantidad'];
                }
            }

            Session::put('carrito', $carrito);
        }

        return redirect()->route('carrito.index')->with('success', 'Carrito actualizado correctamente.');
    }

    public function eliminar($id)
    {
        if (Auth::check()) {
            // Usuario autenticado: eliminar de BD
            $elementoCarrito = ElementoCarrito::findOrFail($id);

            // Verificar que el elemento pertenezca al carrito del usuario
            $carrito = Carrito::where('usuario_id', Auth::id())->firstOrFail();
            if ($elementoCarrito->carrito_id == $carrito->id) {
                $elementoCarrito->delete();
            }
        } else {
            // Usuario no autenticado: eliminar de sesión
            $carrito = Session::get('carrito', []);

            if (isset($carrito[$id])) {
                unset($carrito[$id]);
                Session::put('carrito', $carrito);
            }
        }

        return redirect()->route('carrito.index')->with('success', 'Producto eliminado del carrito.');
    }

    public function aplicarCupon(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string'
        ]);

        $cupon = Cupon::where('codigo', $request->codigo)
                    ->where('inicia_en', '<=', now())
                    ->where('expira_en', '>=', now())
                    ->first();

        if (!$cupon) {
            return redirect()->back()->with('error', 'El cupón no existe o ha expirado.');
        }

        // Guardar el cupón en la sesión
        Session::put('cupon_id', $cupon->id);

        return redirect()->route('carrito.index')->with('success', 'Cupón aplicado correctamente.');
    }

    public function quitarCupon()
    {
        Session::forget('cupon_id');

        return redirect()->route('carrito.index')->with('success', 'Cupón removido.');
    }
}
