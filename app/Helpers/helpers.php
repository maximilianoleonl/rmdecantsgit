<?php

use Illuminate\Support\Facades\Storage;

if (!function_exists('get_imagen_url')) {
    /**
     * Obtener URL de imagen con fallback a imagen por defecto
     *
     * @param string|null $ruta Ruta de la imagen
     * @param string $tipo Tipo de imagen (producto, marca)
     * @return string URL de la imagen
     */
    function get_imagen_url($ruta, $tipo = 'producto')
    {
        if ($ruta && Storage::disk('public')->exists($ruta)) {
            return asset('storage/' . $ruta);
        }

        // Imágenes por defecto para diferentes tipos
        switch ($tipo) {
            case 'marca':
                return asset('img/default-brand.png');
            case 'perfume':
                return asset('img/default-perfume.png');
            case 'decant':
                return asset('img/default-decant.png');
            case 'usuario':
                return asset('img/default-user.png');
            case 'producto':
            default:
                return asset('img/default-product.png');
        }
    }
}

if (!function_exists('get_imagen_producto')) {
    /**
     * Obtener URL de la imagen principal de un producto
     *
     * @param \App\Models\Producto $producto
     * @return string URL de la imagen
     */
    function get_imagen_producto($producto)
    {
        $imagenPrincipal = $producto->imagenPrincipal;
        return get_imagen_url($imagenPrincipal ? $imagenPrincipal->ruta_imagen : null, 'producto');
    }
}

if (!function_exists('get_logo_marca')) {
    /**
     * Obtener URL del logo de una marca
     *
     * @param \App\Models\Marca $marca
     * @return string URL del logo
     */
    function get_logo_marca($marca)
    {
        return get_imagen_url($marca->logo, 'marca');
    }
}
