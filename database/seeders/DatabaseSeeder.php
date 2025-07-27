<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\PizzaSize;
use App\Models\Ingredient;
use App\Models\Pizza;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario admin
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password1234'),
        ]);

        // Crear categorías
        $pizzaCategory = Category::create(['name' => 'Pizzas']);
        $bebidaCategory = Category::create(['name' => 'Bebidas']);

        // Crear tamaños de pizza
        PizzaSize::create([
            'name' => 'Personal',
            'description' => '20cm de diámetro - Ideal para 1 persona',
            'price_multiplier' => 1.00,
        ]);
        PizzaSize::create([
            'name' => 'Mediano',
            'description' => '30cm de diámetro - Ideal para 2-3 personas',
            'price_multiplier' => 1.50,
        ]);
        PizzaSize::create([
            'name' => 'Familiar',
            'description' => '40cm de diámetro - Ideal para 4-6 personas',
            'price_multiplier' => 2.20,
        ]);

        // Crear ingredientes
        $ingredientes = [
            'Salsa de tomate',
            'Queso mozzarella',
            'Albahaca fresca',
            'Jamón',
            'Pepperoni',
            'Pimiento rojo',
            'Pimiento verde',
            'Champiñones',
            'Cebolla',
            'Aceitunas negras',
            'Tomate',
            'Orégano',
        ];

        $ingredientesCreados = [];
        foreach ($ingredientes as $ingrediente) {
            $ingredientesCreados[$ingrediente] = Ingredient::create(['name' => $ingrediente]);
        }

        // Crear productos (pizzas)
        $pizzaMargarita = Product::create([
            'name' => 'Pizza Margarita',
            'description' => 'Clásica pizza italiana con ingredientes frescos y tradicionales',
            'base_price' => 12.00,
            'available' => true,
            'category_id' => $pizzaCategory->id,
        ]);

        $pizzaNYStyle = Product::create([
            'name' => 'Pizza NY Style',
            'description' => 'Pizza estilo neoyorquino con jamón y pepperoni, perfecta para los amantes de la carne',
            'base_price' => 15.00,
            'available' => true,
            'category_id' => $pizzaCategory->id,
        ]);

        $pizzaVegetariana = Product::create([
            'name' => 'Pizza Vegetariana',
            'description' => 'Rica combinación de vegetales frescos para una experiencia saludable y deliciosa',
            'base_price' => 14.00,
            'available' => true,
            'category_id' => $pizzaCategory->id,
        ]);

        // Crear productos (bebidas)
        Product::create([
            'name' => 'Coca Cola Lata',
            'description' => 'Coca Cola 355ml',
            'base_price' => 2.00,
            'available' => true,
            'category_id' => $bebidaCategory->id,
        ]);

        Product::create([
            'name' => 'Coca Cola 1.5L',
            'description' => 'Coca Cola 1.5 litros',
            'base_price' => 4.50,
            'available' => true,
            'category_id' => $bebidaCategory->id,
        ]);

        Product::create([
            'name' => 'Agua 1L',
            'description' => 'Agua purificada 1 litro',
            'base_price' => 1.50,
            'available' => true,
            'category_id' => $bebidaCategory->id,
        ]);

        // Crear pizzas y asociar ingredientes
        $pizzaMargaritaEntity = Pizza::create(['product_id' => $pizzaMargarita->id]);
        $pizzaMargaritaEntity->ingredients()->attach([
            $ingredientesCreados['Salsa de tomate']->id,
            $ingredientesCreados['Queso mozzarella']->id,
            $ingredientesCreados['Albahaca fresca']->id,
        ]);

        $pizzaNYStyleEntity = Pizza::create(['product_id' => $pizzaNYStyle->id]);
        $pizzaNYStyleEntity->ingredients()->attach([
            $ingredientesCreados['Salsa de tomate']->id,
            $ingredientesCreados['Queso mozzarella']->id,
            $ingredientesCreados['Jamón']->id,
            $ingredientesCreados['Pepperoni']->id,
            $ingredientesCreados['Cebolla']->id,
        ]);

        $pizzaVegetarianaEntity = Pizza::create(['product_id' => $pizzaVegetariana->id]);
        $pizzaVegetarianaEntity->ingredients()->attach([
            $ingredientesCreados['Salsa de tomate']->id,
            $ingredientesCreados['Queso mozzarella']->id,
            $ingredientesCreados['Pimiento rojo']->id,
            $ingredientesCreados['Pimiento verde']->id,
            $ingredientesCreados['Champiñones']->id,
            $ingredientesCreados['Cebolla']->id,
            $ingredientesCreados['Aceitunas negras']->id,
            $ingredientesCreados['Tomate']->id,
            $ingredientesCreados['Orégano']->id,
        ]);
    }
}
