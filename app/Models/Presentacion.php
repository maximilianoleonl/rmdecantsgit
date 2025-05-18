<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presentacion extends Model
{
    use HasFactory;

    protected $table = 'presentaciones';

    protected $fillable = [
        'volumen', 'unidad'
    ];

    // Relaciones
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_presentaciones', 'presentacion_id', 'producto_id')
                    ->withPivot('precio', 'stock')
                    ->withTimestamps();
    }

    public function productoPresentaciones()
    {
        return $this->hasMany(ProductoPresentacion::class, 'presentacion_id');
    }

    // Accesorios
    public function getNombreCompletoAttribute()
    {
        return $this->volumen . ' ' . $this->unidad;
    }
}
