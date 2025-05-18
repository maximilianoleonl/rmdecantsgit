<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaccionPunto extends Model
{
    use HasFactory;

    protected $table = 'transacciones_puntos';

    protected $fillable = [
        'usuario_id', 'pedido_id', 'puntos', 'descripcion'
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
