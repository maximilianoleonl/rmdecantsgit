<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuponUsuario extends Model
{
    use HasFactory;

    protected $table = 'cupon_usuarios';

    protected $fillable = [
        'cupon_id', 'usuario_id'
    ];

    // Relaciones
    public function cupon()
    {
        return $this->belongsTo(Cupon::class, 'cupon_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
