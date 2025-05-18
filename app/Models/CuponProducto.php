<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuponProducto extends Model
{
    use HasFactory;

    protected $table = 'cupon_productos';

    protected $fillable = [
        'cupon_id', 'producto_id'
    ];

    // Relaciones
    public function cupon()
    {
        return $this->belongsTo(Cupon::class, 'cupon_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
