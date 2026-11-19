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
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Creamos las categorías primero
        $categorias = [
            'Laptops y Equipos Armados' => 'Laptops y computadoras listas para usar.',
            'Monitores y Pantallas' => 'Monitores para oficina, diseño y gaming.',
            'Componentes de PC' => 'Procesadores, tarjetas de video, memorias, almacenamiento y fuentes.',
            'Accesorios y Periféricos' => 'Mouse, teclados, audífonos y otros accesorios.',
            'Consolas y Videojuegos' => 'Consolas y accesorios para jugar.',
            'Herramientas y Refacciones' => 'Herramientas para armar y reparar equipos.',
        ];

        foreach ($categorias as $nombre => $descripcion) {
            Category::create([
                'name' => $nombre,
                'slug' => str($nombre)->slug(),
                'description' => $descripcion,
            ]);
        }

        // 1b. Catálogos del modelo de datos: marcas, métodos de envío, métodos de pago y proveedores
        $marcas = [
            'HP' => 'Estados Unidos', 'ASUS' => 'Taiwán', 'Kingston' => 'Estados Unidos',
            'Logitech' => 'Suiza', 'Samsung' => 'Corea del Sur', 'Sony' => 'Japón',
            'Lenovo' => 'China', 'AMD' => 'Estados Unidos', 'Corsair' => 'Estados Unidos',
            'iFixit' => 'Estados Unidos',
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

        // 2. PRODUCTOS DEL CATÁLOGO
        // El stock de todos empieza en 0: las unidades entran al inventario solo a través de una
        // orden de compra a un proveedor marcada como «recibida» (RN1). Las fotos están en
        // storage/app/public/productos y se publican con `php artisan storage:link`.
        $catalogo = [
            // clave => [categoría, marca, nombre, precio, foto, descripción]
            'victus' => ['Laptops y Equipos Armados', 'HP', 'Laptop Gamer HP Victus 15', 16999.00, 'victus.jpg',
                'Equipo portátil de alto rendimiento para gaming y desarrollo. Cuenta con refrigeración optimizada y pantalla de alta tasa de refresco.'],
            'vivobook' => ['Laptops y Equipos Armados', 'ASUS', 'Laptop ASUS Vivobook 15', 11499.00, 'vivobook-15.jpg',
                'Laptop ligera de 15.6 pulgadas para escuela y oficina, con teclado completo y pantalla Full HD.'],
            'ideapad' => ['Laptops y Equipos Armados', 'Lenovo', 'Laptop Lenovo IdeaPad Slim 3', 10999.00, 'ideapad-slim-3.jpg',
                'Laptop delgada con procesador AMD Ryzen, teclado retroiluminado y lector de huella para el día a día.'],
            'rtx' => ['Componentes de PC', 'ASUS', 'Tarjeta de Video ASUS TUF Gaming GeForce RTX 5060 Ti', 8499.00, 'rtx.jpg',
                'Gráficos de ultra alto rendimiento con sistema de refrigeración de triple ventilador y componentes de grado militar para máxima durabilidad.'],
            'ram' => ['Componentes de PC', 'Kingston', 'Memoria RAM Kingston FURY DDR5 16GB', 2399.00, 'ram.jpg',
                'Módulo de memoria de última generación a 5200MHz con disipador de calor de bajo perfil para un rendimiento extremo en multitarea.'],
            'ryzen' => ['Componentes de PC', 'AMD', 'Procesador AMD Ryzen 5 9600X', 4999.00, 'ryzen-5-9600x.jpg',
                'Procesador de 6 núcleos y 12 hilos para tarjetas madre AM5, ideal para gaming y creación de contenido.'],
            'ssd' => ['Componentes de PC', 'Samsung', 'SSD Samsung 990 EVO 1TB', 1799.00, 'samsung-990-evo.jpg',
                'Unidad de estado sólido NVMe M.2 de 1 TB para arranques y cargas de juegos mucho más rápidos.'],
            'fuente' => ['Componentes de PC', 'Corsair', 'Fuente de Poder Corsair RM750e', 2299.00, 'corsair-rm750e.jpg',
                'Fuente de 750 W totalmente modular con certificación 80 Plus Gold y ventilador silencioso.'],
            'odyssey' => ['Monitores y Pantallas', 'Samsung', 'Monitor Curvo Samsung Odyssey G5 27"', 5499.00, 'odyssey-g5.jpg',
                'Monitor curvo para gaming con resolución QHD y alta tasa de refresco para partidas fluidas.'],
            'tufmonitor' => ['Monitores y Pantallas', 'ASUS', 'Monitor Curvo ASUS TUF Gaming 23.6"', 3299.00, 'tuf-monitor-24.jpg',
                'Monitor curvo 1500R de 23.6 pulgadas con resolución Full HD, pensado para gaming competitivo.'],
            'mouse' => ['Accesorios y Periféricos', 'Logitech', 'Mouse Inalámbrico Logitech G502 X Plus', 2799.00, 'g502-x-plus.jpg',
                'Mouse inalámbrico para gaming con iluminación RGB, botones programables y sensor de alta precisión.'],
            'teclado' => ['Accesorios y Periféricos', 'ASUS', 'Teclado Mecánico ASUS TUF Gaming K3', 1499.00, 'tuf-k3.jpg',
                'Teclado mecánico con iluminación RGB por tecla y reposamuñecas para largas sesiones de juego.'],
            'audifonos' => ['Accesorios y Periféricos', 'Sony', 'Audífonos Sony WH-1000XM4', 5499.00, 'wh-1000xm4.jpg',
                'Audífonos inalámbricos con cancelación de ruido y batería de larga duración.'],
            'ps5' => ['Consolas y Videojuegos', 'Sony', 'Consola PlayStation 5 Slim', 9999.00, 'ps5-slim.jpg',
                'Consola de nueva generación en formato compacto, con lector de discos y control DualSense.'],
            'ifixit' => ['Herramientas y Refacciones', 'iFixit', 'Kit de Desarmadores de Precisión iFixit Manta', 1599.00, 'ifixit-manta.jpg',
                'Kit de 112 puntas de precisión con mango de aluminio para reparar laptops, consolas y celulares.'],
        ];

        $productos = [];
        foreach ($catalogo as $clave => [$categoria, $marca, $nombre, $precio, $foto, $descripcion]) {
            $productos[$clave] = Product::create([
                'category_id' => Category::where('name', $categoria)->firstOrFail()->id,
                'brand_id' => Brand::where('name', $marca)->firstOrFail()->id,
                'name' => $nombre,
                'slug' => Str::slug($nombre),
                'description' => $descripcion,
                'price' => $precio,
                'stock' => 0,
                'is_active' => true,
            ]);
            $productos[$clave]->image()->create(['url' => 'productos/' . $foto]);
        }

        // 3. ÓRDENES DE COMPRA: de aquí sale todo el inventario inicial (RN1).
        // [proveedor, días atrás del pedido, días atrás de la recepción (null = pendiente), renglones]
        $ordenes = [
            [0, 40, 35, ['victus' => 8, 'vivobook' => 6, 'ideapad' => 5, 'odyssey' => 4, 'tufmonitor' => 6]],
            [1, 33, 28, ['rtx' => 3, 'ram' => 20, 'ryzen' => 10, 'ssd' => 15, 'fuente' => 7]],
            [0, 25, 20, ['mouse' => 25, 'teclado' => 12, 'audifonos' => 9]],
            [1, 16, 12, ['ps5' => 2, 'ifixit' => 18]],
            [0, 15, 'cancelada', ['odyssey' => 3]],   // cancelada: no mueve el inventario
            [1, 2, null, ['rtx' => 5, 'ps5' => 4]],     // pendiente: para recibirla en vivo
        ];

        foreach ($ordenes as [$proveedor, $diasPedido, $recepcion, $renglones]) {
            $orden = PurchaseOrder::create([
                'supplier_id' => $proveedores[$proveedor]->id,
                'ordered_at' => now()->subDays($diasPedido)->toDateString(),
                'status' => PurchaseOrder::PENDING,
            ]);

            foreach ($renglones as $clave => $cantidad) {
                $orden->products()->attach($productos[$clave]->id, [
                    'quantity' => $cantidad,
                    // costo al proveedor: 70 % del precio de venta
                    'unit_cost' => round($productos[$clave]->price * 0.70, 2),
                ]);
            }

            if ($recepcion === 'cancelada') {
                $orden->cancel();
            } elseif ($recepcion !== null) {
                $orden->receive(now()->subDays($recepcion));
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