<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElementoPedido extends Model
{
    use HasFactory;

    protected $table = 'elementos_pedido';

    protected $fillable = [
        'pedido_id', 'producto_presentacion_id', 'precio', 'cantidad'
    ];

    // Relaciones
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function productoPresentacion()
    {
        return $this->belongsTo(ProductoPresentacion::class, 'producto_presentacion_id');
    }

    // Accesorios
    public function getSubtotalAttribute()
    {
        return $this->precio * $this->cantidad;
    }
}
