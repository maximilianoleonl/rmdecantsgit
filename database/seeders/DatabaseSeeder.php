<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
{
    $this->call([
        UsuariosSeeder::class,
        MarcasSeeder::class,
        TiposAromaSeeder::class,
        TiposProductoSeeder::class,
        PresentacionesSeeder::class,
        MetodosPagoSeeder::class,
        ProductosSeeder::class,
    ]);
}
}
