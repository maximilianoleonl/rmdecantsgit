<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'usuario_id', 'direccion_id', 'metodo_pago_id', 'subtotal', 'costo_envio',
        'descuento', 'total', 'cupon_id', 'estado', 'numero_seguimiento',
        'puntos_ganados', 'pagado', 'notas', 'id_pago'
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function direccion()
    {
        return $this->belongsTo(Direccion::class, 'direccion_id');
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id');
    }

    public function cupon()
    {
        return $this->belongsTo(Cupon::class, 'cupon_id');
    }

    public function elementos()
    {
        return $this->hasMany(ElementoPedido::class, 'pedido_id');
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class, 'pedido_id');
    }

    public function transaccionesPuntos()
    {
        return $this->hasMany(TransaccionPunto::class, 'pedido_id');
    }
}
