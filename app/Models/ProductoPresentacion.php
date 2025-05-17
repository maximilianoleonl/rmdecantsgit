<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoPresentacion extends Model
{
    use HasFactory;

    protected $table = 'producto_presentaciones';

    protected $fillable = [
        'producto_id', 'presentacion_id', 'precio', 'stock'
    ];

    // Relaciones
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function presentacion()
    {
        return $this->belongsTo(Presentacion::class, 'presentacion_id');
    }

    public function elementosCarrito()
    {
        return $this->hasMany(ElementoCarrito::class, 'producto_presentacion_id');
    }

    public function elementosPedido()
    {
        return $this->hasMany(ElementoPedido::class, 'producto_presentacion_id');
    }

    // Accesorios
    public function getEsUltimasUnidadesAttribute()
    {
        return $this->stock <= 5 && $this->stock > 0;
    }

    public function getDisponibleAttribute()
    {
        return $this->stock > 0;
    }
}
