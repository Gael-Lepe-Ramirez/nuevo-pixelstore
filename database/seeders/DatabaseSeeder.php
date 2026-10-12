<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\ShippingMethod;
use App\Models\Supplier;
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

        // 1b. Catálogos del modelo de datos: marcas, métodos de envío, métodos de pago y proveedores
        $marcas = [
            'HP' => 'Estados Unidos', 'ASUS' => 'Taiwán', 'Kingston' => 'Estados Unidos',
            'Logitech' => 'Suiza', 'Samsung' => 'Corea del Sur', 'Sony' => 'Japón',
        ];
        foreach ($marcas as $nombre => $pais) {
            Brand::create(['name' => $nombre, 'country' => $pais]);
        }

        foreach ([
            ['name' => 'Paquetería estándar', 'cost' => 99.00, 'estimated_days' => 5],
            ['name' => 'Paquetería exprés', 'cost' => 199.00, 'estimated_days' => 2],
            ['name' => 'Retiro en tienda', 'cost' => 0.00, 'estimated_days' => 0],
        ] as $envio) {
            ShippingMethod::create($envio);
        }

        foreach (['Tarjeta de crédito/débito', 'Transferencia SPEI', 'PayPal', 'Mercado Pago'] as $pago) {
            PaymentMethod::create(['name' => $pago]);
        }

        $proveedores = [
            Supplier::create([
                'name' => 'Distribuidora Tecnológica del Pacífico, S.A. de C.V.',
                'rfc' => 'DTP150312AB1',
                'email' => 'ventas@dtpacifico.com.mx',
                'phone' => '3336541200',
                'address' => 'Av. Patria 2450, Zapopan, Jalisco',
            ]),
            Supplier::create([
                'name' => 'Mayoristas de Cómputo de Occidente, S.A. de C.V.',
                'rfc' => 'MCO120708XY4',
                'email' => 'compras@mcoccidente.com.mx',
                'phone' => '3338127700',
                'address' => 'Calz. Lázaro Cárdenas 1800, Guadalajara, Jalisco',
            ]),
        ];

        // Las unidades iniciales NO se escriben a mano en `stock`: entran al inventario a través
        // de una orden de compra a un proveedor marcada como «recibida» (RN1).
        $unidades = []; // product_id => unidades a pedir

        // 2. PRODUCTOS REALES
        
        // Producto 1: Laptop HP Victus 15
        $prod1 = Product::create([
            'category_id' => Category::where('name', 'Laptops y Equipos Armados')->first()->id,
            'brand_id' => Brand::where('name', 'HP')->first()->id,
            'name' => 'Laptop Gamer HP Victus 15',
            'slug' => 'laptop-gamer-hp-victus-15',
            'description' => 'Equipo portátil de alto rendimiento para gaming y desarrollo. Cuenta con refrigeración optimizada y pantalla de alta tasa de refresco.',
            'price' => 16999.00,
            'stock' => 0,
            'is_active' => true,
        ]);
        $prod1->image()->create(['url' => 'productos/victus.jpg']);
        $unidades[$prod1->id] = 5;

        // Producto 2: Tarjeta Gráfica RTX 5060 Ti
        $prod2 = Product::create([
            'category_id' => Category::where('name', 'Componentes de PC')->first()->id,
            'brand_id' => Brand::where('name', 'ASUS')->first()->id,
            'name' => 'Tarjeta de Video ASUS TUF Gaming GeForce RTX 5060 Ti',
            'slug' => 'tarjeta-de-video-asus-tuf-gaming-geforce-rtx-5060-ti',
            'description' => 'Gráficos de ultra alto rendimiento con sistema de refrigeración de triple ventilador y componentes de grado militar para máxima durabilidad.',
            'price' => 8499.00,
            'stock' => 0,
            'is_active' => true,
        ]);
        $prod2->image()->create(['url' => 'productos/rtx.jpg']);
        $unidades[$prod2->id] = 3;

        // Producto 3: RAM Kingston FURY DDR5
        $prod3 = Product::create([
            'category_id' => Category::where('name', 'Componentes de PC')->first()->id,
            'brand_id' => Brand::where('name', 'Kingston')->first()->id,
            'name' => 'Memoria RAM Kingston FURY DDR5 16GB',
            'slug' => 'memoria-ram-kingston-fury-beast-ddr5-16gb',
            'description' => 'Módulo de memoria de última generación a 5200MHz con disipador de calor de bajo perfil para un rendimiento extremo en multitarea.',
            'price' => 2399.00,
            'stock' => 0,
            'is_active' => true,
        ]);
        $prod3->image()->create(['url' => 'productos/ram.jpg']);
        $unidades[$prod3->id] = 20;

        // 3. Generamos el resto de productos de relleno con el Factory
        $todasLasCategorias = Category::all();
        foreach ($todasLasCategorias as $cat) {
            $productosRelleno = Product::factory(4)->create([
                'category_id' => $cat->id,
                'stock' => 0,
            ]);

            // A cada producto inventado le asignamos la foto genérica de hardware
            foreach ($productosRelleno as $producto) {
                if ($producto->image) {
                    $producto->image()->delete();
                }
                $producto->image()->create(['url' => 'productos/generico.jpg']);
                $unidades[$producto->id] = fake()->numberBetween(5, 40);
            }
        }

        // 3b. Órdenes de compra recibidas: de aquí sale el inventario inicial.
        foreach (array_chunk($unidades, 12, true) as $i => $lote) {
            $orden = PurchaseOrder::create([
                'supplier_id' => $proveedores[$i % count($proveedores)]->id,
                'ordered_at' => now()->subDays(10)->toDateString(),
                'status' => PurchaseOrder::PENDING,
            ]);

            foreach ($lote as $productoId => $cantidad) {
                $orden->products()->attach($productoId, [
                    'quantity' => $cantidad,
                    // costo al proveedor: 70 % del precio de venta
                    'unit_cost' => round(Product::find($productoId)->price * 0.70, 2),
                ]);
            }

            $orden->receive(now()->subDays(5));
        }

        // 4. Creamos el usuario administrador
        User::factory()->create([
            'name' => 'Admin PixelStore',
            'email' => 'admin@pixelstore.com',
            'role' => 'admin',
        ]);
    }
}