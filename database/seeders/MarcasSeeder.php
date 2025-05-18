<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Marca;

class MarcasSeeder extends Seeder
{
    public function run()
    {
        $marcas = [
            ['nombre' => 'Dior', 'slug' => 'dior', 'descripcion' => 'Marca de lujo francesa'],
            ['nombre' => 'Chanel', 'slug' => 'chanel', 'descripcion' => 'Icónica marca de moda y fragancias'],
            ['nombre' => 'Tom Ford', 'slug' => 'tom-ford', 'descripcion' => 'Fragancias de lujo'],
            ['nombre' => 'Versace', 'slug' => 'versace', 'descripcion' => 'Marca italiana de alta costura'],
            ['nombre' => 'Armani', 'slug' => 'armani', 'descripcion' => 'Elegancia y estilo italiano'],
        ];

        foreach ($marcas as $marca) {
            Marca::create($marca);
        }
    }
}
