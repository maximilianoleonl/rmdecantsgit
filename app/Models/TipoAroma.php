<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoAroma extends Model
{
    use HasFactory;

    protected $table = 'tipos_aroma';

    protected $fillable = [
        'nombre', 'descripcion'
    ];

    // Relaciones
    public function productos()
    {
        return $this->hasMany(Producto::class, 'tipo_aroma_id');
    }
}
