<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;

    protected $table = 'carritos';

    protected $fillable = [
        'usuario_id'
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function elementos()
    {
        return $this->hasMany(ElementoCarrito::class, 'carrito_id');
    }

    // Accesorios
    public function getSubtotalAttribute()
    {
        return $this->elementos->sum(function($elemento) {
            return $elemento->cantidad * $elemento->productoPresentacion->precio;
        });
    }

    public function getCostoEnvioAttribute()
    {
        return $this->subtotal >= 2000 ? 0 : 150; // Envío gratis si es mayor a $2000 MXN
    }

    public function getTotalAttribute()
    {
        return $this->subtotal + $this->costo_envio;
    }

    public function getCantidadTotalAttribute()
    {
        return $this->elementos->sum('cantidad');
    }
}
