<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
    use HasFactory;

    protected $table = 'direcciones';

    protected $fillable = [
        'usuario_id', 'calle', 'numero', 'numero_interior', 'colonia',
        'ciudad', 'estado', 'pais', 'codigo_postal', 'telefono', 'es_predeterminada'
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'direccion_id');
    }

    // Accesorios
    public function getDireccionCompletaAttribute()
    {
        $direccion = $this->calle . ' ' . $this->numero;

        if ($this->numero_interior) {
            $direccion .= ' Int. ' . $this->numero_interior;
        }

        $direccion .= ', ' . $this->colonia . ', ' . $this->ciudad . ', ' . $this->estado . ', ' . $this->pais . ', CP ' . $this->codigo_postal;

        return $direccion;
    }
}
