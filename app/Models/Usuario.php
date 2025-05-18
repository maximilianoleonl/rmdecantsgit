<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// Eliminamos la importación de HasApiTokens

class Usuario extends Authenticatable
{
    use Notifiable; // Quitamos HasApiTokens

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre', 'email', 'password', 'id_social', 'tipo_social', 'puntos'
    ];

    protected $hidden = [
        'password', 'token_recuerdo',
    ];

    protected $casts = [
        'email_verificado_en' => 'datetime',
    ];

    public function direcciones()
    {
        return $this->hasMany(Direccion::class, 'usuario_id');
    }

    public function carrito()
    {
        return $this->hasOne(Carrito::class, 'usuario_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'usuario_id');
    }

    public function listaDeseos()
    {
        return $this->hasMany(ListaDeseo::class, 'usuario_id');
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class, 'usuario_id');
    }

    public function cupones()
    {
        return $this->belongsToMany(Cupon::class, 'cupon_usuarios', 'usuario_id', 'cupon_id');
    }

    public function transaccionesPuntos()
    {
        return $this->hasMany(TransaccionPunto::class, 'usuario_id');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'usuario_id' );
    }
}
