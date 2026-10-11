<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Creamos las categorías primero
        $categorias = [
            'Laptops y Equipos Armados',
            'Monitores y Pantallas',
            'Componentes de PC',
            'Accesorios y Periféricos',
            'Consolas y Videojuegos',
            'Herramientas y Refacciones'
        ];

        foreach ($categorias as $nombre) {
            Category::create([
                'name' => $nombre,
                'slug' => str($nombre)->slug(),
            ]);
        }

        // 2. PRODUCTOS REALES
        
        // Producto 1: Laptop HP Victus 15
        $prod1 = Product::create([
            'category_id' => Category::where('name', 'Laptops y Equipos Armados')->first()->id,
            'name' => 'Laptop Gamer HP Victus 15',
            'slug' => 'laptop-gamer-hp-victus-15',
            'description' => 'Equipo portátil de alto rendimiento para gaming y desarrollo. Cuenta con refrigeración optimizada y pantalla de alta tasa de refresco.',
            'price' => 16999.00,
            'stock' => 5,
            'is_active' => true,
        ]);
        $prod1->image()->create(['url' => 'productos/victus.jpg']);

        // Producto 2: Tarjeta Gráfica RTX 5060 Ti
        $prod2 = Product::create([
            'category_id' => Category::where('name', 'Componentes de PC')->first()->id,
            'name' => 'Tarjeta de Video ASUS TUF Gaming GeForce RTX 5060 Ti',
            'slug' => 'tarjeta-de-video-asus-tuf-gaming-geforce-rtx-5060-ti',
            'description' => 'Gráficos de ultra alto rendimiento con sistema de refrigeración de triple ventilador y componentes de grado militar para máxima durabilidad.',
            'price' => 8499.00,
            'stock' => 3,
            'is_active' => true,
        ]);
        $prod2->image()->create(['url' => 'productos/rtx.jpg']);

        // Producto 3: RAM Kingston FURY DDR5
        $prod3 = Product::create([
            'category_id' => Category::where('name', 'Componentes de PC')->first()->id,
            'name' => 'Memoria RAM Kingston FURY DDR5 16GB',
            'slug' => 'memoria-ram-kingston-fury-beast-ddr5-16gb',
            'description' => 'Módulo de memoria de última generación a 5200MHz con disipador de calor de bajo perfil para un rendimiento extremo en multitarea.',
            'price' => 2399.00,
            'stock' => 20,
            'is_active' => true,
        ]);
        $prod3->image()->create(['url' => 'productos/ram.jpg']);

        // 3. Generamos el resto de productos de relleno con el Factory
        $todasLasCategorias = Category::all();
        foreach ($todasLasCategorias as $cat) {
            $productosRelleno = Product::factory(4)->create([
                'category_id' => $cat->id
            ]);

            // A cada producto inventado le asignamos la foto genérica de hardware
            foreach ($productosRelleno as $producto) {
                if ($producto->image) {
                    $producto->image()->delete();
                }
                $producto->image()->create(['url' => 'productos/generico.jpg']);
            }
        }

        // 4. Creamos el usuario administrador
        User::factory()->create([
            'name' => 'Admin PixelStore',
            'email' => 'admin@pixelstore.com',
            'role' => 'admin',
        ]);
    }
}