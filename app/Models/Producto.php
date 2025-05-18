<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'nombre', 'slug', 'marca_id', 'tipo_aroma_id', 'tipo_producto_id', 'descripcion', 'porcentaje_contenido'
    ];

    // Relaciones
    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function tipoAroma()
    {
        return $this->belongsTo(TipoAroma::class, 'tipo_aroma_id');
    }

    public function tipoProducto()
    {
        return $this->belongsTo(TipoProducto::class, 'tipo_producto_id');
    }

    public function imagenes()
    {
        return $this->hasMany(ImagenProducto::class, 'producto_id');
    }

    public function imagenPrincipal()
    {
        return $this->hasOne(ImagenProducto::class, 'producto_id')->where('es_principal', true);
    }

    public function presentaciones()
    {
        return $this->belongsToMany(Presentacion::class, 'producto_presentaciones', 'producto_id', 'presentacion_id')
                    ->withPivot('precio', 'stock')
                    ->withTimestamps();
    }

    public function productoPresentaciones()
    {
        return $this->hasMany(ProductoPresentacion::class, 'producto_id');
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class, 'producto_id');
    }

    public function cupones()
    {
        return $this->belongsToMany(Cupon::class, 'cupon_productos', 'producto_id', 'cupon_id');
    }

    public function usuariosQueLoDesean()
    {
        return $this->belongsToMany(Usuario::class, 'lista_deseos', 'producto_id', 'usuario_id');
    }

    // Accesorios
    public function getCalificacionPromedioAttribute()
    {
        return $this->resenas()->where('esta_aprobada', true)->avg('calificacion') ?? 0;
    }

    public function getStockDisponibleAttribute()
    {
        return $this->productoPresentaciones->sum('stock');
    }

    public function getEsUltimasUnidadesAttribute()
    {
        return $this->stock_disponible <= 5 && $this->stock_disponible > 0;
    }
}
