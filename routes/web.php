<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\DireccionController;
use App\Http\Controllers\ResenaController;
use App\Http\Controllers\ListaDeseoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminProductoController;
use App\Http\Controllers\Admin\AdminMarcaController;
use App\Http\Controllers\Admin\AdminPedidoController;
use App\Http\Controllers\Admin\AdminUsuarioController;
use App\Http\Controllers\Admin\AdminCuponController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas
Route::get('/', [HomeController::class, 'index'])->name('home');

// Catálogo de productos
Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');
Route::get('/catalogo/marca/{slug}', [CatalogoController::class, 'porMarca'])->name('catalogo.marca');
Route::get('/catalogo/aroma/{slug}', [CatalogoController::class, 'porAroma'])->name('catalogo.aroma');
Route::get('/catalogo/tipo/{slug}', [CatalogoController::class, 'porTipo'])->name('catalogo.tipo');
Route::get('/buscar', [CatalogoController::class, 'buscar'])->name('catalogo.buscar');

// Detalles de producto
Route::get('/producto/{slug}', [ProductoController::class, 'show'])->name('producto.detalle');

// Reseñas
Route::post('/producto/{id}/resena', [ResenaController::class, 'store'])->name('resena.store')->middleware('auth');

// Rutas de autenticación personalizadas (si quieres usar nombres en español)
Route::get('/iniciar-sesion', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/iniciar-sesion', [LoginController::class, 'login']);
Route::post('/cerrar-sesion', [LoginController::class, 'logout'])->name('logout');

Route::get('/registro', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/registro', [RegisterController::class, 'register']);

Route::get('/recuperar-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/recuperar-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// Rutas del carrito de compras (no requieren autenticación)
Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
Route::put('/carrito/actualizar', [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
Route::delete('/carrito/eliminar/{id}', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');
Route::post('/carrito/aplicar-cupon', [CarritoController::class, 'aplicarCupon'])->name('carrito.aplicar-cupon');
Route::post('/carrito/quitar-cupon', [CarritoController::class, 'quitarCupon'])->name('carrito.quitar-cupon');

// Grupo de rutas que requieren autenticación
Route::middleware(['auth'])->group(function () {

    // Perfil de usuario
    Route::get('/mi-cuenta', [UsuarioController::class, 'cuenta'])->name('usuario.cuenta');
    Route::put('/mi-cuenta/actualizar', [UsuarioController::class, 'actualizar'])->name('usuario.actualizar');
    Route::put('/mi-cuenta/cambiar-password', [UsuarioController::class, 'cambiarPassword'])->name('usuario.cambiar-password');

    // Direcciones
    Route::get('/mi-cuenta/direcciones', [DireccionController::class, 'index'])->name('direcciones.index');
    Route::get('/mi-cuenta/direcciones/crear', [DireccionController::class, 'create'])->name('direcciones.create');
    Route::post('/mi-cuenta/direcciones', [DireccionController::class, 'store'])->name('direcciones.store');
    Route::get('/mi-cuenta/direcciones/{id}/editar', [DireccionController::class, 'edit'])->name('direcciones.edit');
    Route::put('/mi-cuenta/direcciones/{id}', [DireccionController::class, 'update'])->name('direcciones.update');
    Route::delete('/mi-cuenta/direcciones/{id}', [DireccionController::class, 'destroy'])->name('direcciones.destroy');
    Route::post('/mi-cuenta/direcciones/{id}/predeterminada', [DireccionController::class, 'hacerPredeterminada'])->name('direcciones.predeterminada');

    // Lista de deseos
    Route::get('/mi-cuenta/favoritos', [ListaDeseoController::class, 'index'])->name('favoritos.index');
    Route::post('/favoritos/agregar/{producto_id}', [ListaDeseoController::class, 'agregar'])->name('favoritos.agregar');
    Route::delete('/favoritos/eliminar/{producto_id}', [ListaDeseoController::class, 'eliminar'])->name('favoritos.eliminar');

    // Pedidos
    Route::get('/mi-cuenta/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/mi-cuenta/pedidos/{id}', [PedidoController::class, 'show'])->name('pedidos.show');

    // Puntos
    Route::get('/mi-cuenta/puntos', [UsuarioController::class, 'puntos'])->name('usuario.puntos');

    // Proceso de checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/procesar', [CheckoutController::class, 'procesar'])->name('checkout.procesar');
    Route::get('/checkout/confirmar', [CheckoutController::class, 'confirmar'])->name('checkout.confirmar');
    Route::get('/checkout/exito/{id}', [CheckoutController::class, 'exito'])->name('checkout.exito');

    // Métodos de pago (procesar pagos)
    Route::post('/checkout/procesar-pago', [CheckoutController::class, 'procesarPago'])->name('checkout.procesar-pago');

    // Reseñas - Requiere autenticación
    Route::get('/mi-cuenta/reseñas', [ResenaController::class, 'misReseñas'])->name('resenas.mis-resenas');
});

// Rutas para administración (panel de control)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminController::class, 'index'])->name('index');

    // Gestión de productos
    Route::resource('productos', AdminProductoController::class);
    Route::get('productos/{id}/imagenes', [AdminProductoController::class, 'imagenes'])->name('productos.imagenes');
    Route::post('productos/{id}/imagenes', [AdminProductoController::class, 'subirImagen'])->name('productos.subir-imagen');
    Route::delete('productos/imagen/{id}', [AdminProductoController::class, 'eliminarImagen'])->name('productos.eliminar-imagen');
    Route::post('productos/imagen/{id}/principal', [AdminProductoController::class, 'hacerImagenPrincipal'])->name('productos.imagen-principal');

    // Gestión de presentaciones
    Route::get('productos/{id}/presentaciones', [AdminProductoController::class, 'presentaciones'])->name('productos.presentaciones');
    Route::post('productos/{id}/presentaciones', [AdminProductoController::class, 'guardarPresentacion'])->name('productos.guardar-presentacion');
    Route::put('productos/presentacion/{id}', [AdminProductoController::class, 'actualizarPresentacion'])->name('productos.actualizar-presentacion');
    Route::delete('productos/presentacion/{id}', [AdminProductoController::class, 'eliminarPresentacion'])->name('productos.eliminar-presentacion');

    // Gestión de marcas
    Route::resource('marcas', AdminMarcaController::class);

    // Gestión de pedidos
    Route::resource('pedidos', AdminPedidoController::class)->except(['create', 'store', 'destroy']);
    Route::put('pedidos/{id}/estado', [AdminPedidoController::class, 'cambiarEstado'])->name('pedidos.cambiar-estado');
    Route::put('pedidos/{id}/numero-seguimiento', [AdminPedidoController::class, 'actualizarNumeroSeguimiento'])->name('pedidos.actualizar-seguimiento');

    // Gestión de usuarios
    Route::resource('usuarios', AdminUsuarioController::class);

    // Gestión de cupones
    Route::resource('cupones', AdminCuponController::class);

    // Reseñas
    Route::get('resenas', [AdminController::class, 'resenas'])->name('resenas.index');
    Route::put('resenas/{id}/aprobar', [AdminController::class, 'aprobarResena'])->name('resenas.aprobar');
    Route::put('resenas/{id}/rechazar', [AdminController::class, 'rechazarResena'])->name('resenas.rechazar');
    Route::delete('resenas/{id}', [AdminController::class, 'eliminarResena'])->name('resenas.eliminar');

    // Estadísticas
    Route::get('estadisticas', [AdminController::class, 'estadisticas'])->name('estadisticas');
    Route::get('estadisticas/ventas', [AdminController::class, 'estadisticasVentas'])->name('estadisticas.ventas');
    Route::get('estadisticas/productos', [AdminController::class, 'estadisticasProductos'])->name('estadisticas.productos');
    Route::get('estadisticas/usuarios', [AdminController::class, 'estadisticasUsuarios'])->name('estadisticas.usuarios');

    // Rutas para autenticación social
Route::get('login/google', [App\Http\Controllers\SocialAuthController::class, 'redirectToGoogle'])->name('login.google');
Route::get('login/google/callback', [App\Http\Controllers\SocialAuthController::class, 'handleGoogleCallback']);

Route::get('login/facebook', [App\Http\Controllers\SocialAuthController::class, 'redirectToFacebook'])->name('login.facebook');
Route::get('login/facebook/callback', [App\Http\Controllers\SocialAuthController::class, 'handleFacebookCallback']);
// Rutas para autenticación social
Route::get('login/{provider}', [App\Http\Controllers\Auth\LoginController::class, 'redirectToProvider'])->name('login.social');
Route::get('login/{provider}/callback', [App\Http\Controllers\Auth\LoginController::class, 'handleProviderCallback']);

// Ruta para obtener imágenes de un producto en formato JSON
Route::get('/producto/{id}/imagenes', [ProductoController::class, 'imagenesProductoJson'])->name('producto.imagenes-json');
});
