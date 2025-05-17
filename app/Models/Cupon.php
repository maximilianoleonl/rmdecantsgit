<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cupon extends Model
{
    use HasFactory;

    protected $table = 'cupones';

    protected $fillable = [
        'codigo', 'tipo', 'valor', 'compra_minima', 'es_uso_unico', 'inicia_en', 'expira_en'
    ];

    protected $casts = [
        'inicia_en' => 'datetime',
        'expira_en' => 'datetime',
    ];

    // Relaciones
    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'cupon_usuarios', 'cupon_id', 'usuario_id');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'cupon_productos', 'cupon_id', 'producto_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'cupon_id');
    }

    // Accesorios
    public function getEstaActivoAttribute()
    {
        $now = now();
        return $this->inicia_en <= $now && $this->expira_en >= $now;
    }
}
