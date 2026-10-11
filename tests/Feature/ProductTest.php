<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    // Esta línea asegura que la base de datos se limpie después de cada prueba
    use RefreshDatabase;

    /**
     * Prueba 1: Consulta ruta GET y asegura código 200 y texto.
     */
    public function test_usuario_puede_ver_catalogo_y_texto()
    {
        // Simulamos un usuario
        $user = User::factory()->create();

        // 1. Actuar: Consultamos la ruta Y (index de productos)
        $response = $this->actingAs($user)->get(route('products.index'));

        // 2. Afirmar: Aseguramos código 200 y un texto determinado
        $response->assertStatus(200);
        $response->assertSee('Productos Disponibles'); 
    }

    /**
     * Prueba 2: Petición POST, asegura creación en DB y redireccionamiento.
     */
    public function test_usuario_admin_puede_crear_producto_con_redireccion()
    {
        // Creamos un admin y una categoría requerida
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        // 1. Actuar: Petición POST con datos válidos
        $response = $this->actingAs($admin)->post(route('products.store'), [
            'name' => 'Tarjeta Gráfica RTX 4090',
            'category_id' => $category->id,
            'price' => 25000.50,
            'stock' => 5,
            'description' => 'Alta potencia para gaming.',
        ]);

        // 2. Afirmar: Redireccionamiento y existencia en BD
        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'name' => 'Tarjeta Gráfica RTX 4090',
        ]);
    }

    /**
     * Prueba 3: Petición POST con error, asegura fallo en validación.
     */
    public function test_creacion_falla_por_errores_de_validacion()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // 1. Actuar: Petición POST con datos incompletos o incorrectos (sin nombre)
        $response = $this->actingAs($admin)->post(route('products.store'), [
            'price' => 'precio_invalido', // <- Un texto en vez de número
        ]);

        // 2. Afirmar: Aseguramos error de validación
        $response->assertSessionHasErrors(['name', 'price', 'category_id']);
    }

    /**
     * Prueba 4: Petición DELETE, asegura eliminación de registro y redireccionamiento.
     */
    public function test_usuario_admin_puede_eliminar_producto()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        // Preparamos un producto y su categoría
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        // Aseguramos que existe antes de borrar
        $this->assertDatabaseHas('products', ['id' => $product->id]);

        // 1. Actuar: Petición DELETE
        $response = $this->actingAs($admin)->delete(route('products.destroy', $product->id));

        // 2. Afirmar: Redireccionamiento y eliminación en DB
        $response->assertRedirect(route('products.index'));
        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }
}