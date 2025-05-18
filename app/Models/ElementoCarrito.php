<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElementoCarrito extends Model
{
    use HasFactory;

    protected $table = 'elementos_carrito';

    protected $fillable = [
        'carrito_id', 'producto_presentacion_id', 'cantidad'
    ];

    // Relaciones
    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'carrito_id');
    }

    public function productoPresentacion()
    {
        return $this->belongsTo(ProductoPresentacion::class, 'producto_presentacion_id');
    }

    // Accesorios
    public function getSubtotalAttribute()
    {
        return $this->cantidad * $this->productoPresentacion->precio;
    }
}
