<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        Sanctum::actingAs($this->user);
    }

    #[Test]
    public function authenticated_user_can_create_product(): void
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $response = $this->postJson(route('products.store'), [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'name' => 'iPhone 16',
            'sku' => 'IPHONE-16',
            'description' => 'Latest iPhone',
            'price' => 99999,
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Product created successfully.',
                'data' => [
                    'category_id' => $category->id,
                    'supplier_id' => $supplier->id,
                    'category' => [
                        'id' => $category->id,
                    ],
                    'supplier' => [
                        'id' => $supplier->id,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'sku' => 'IPHONE-16',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
    }

    #[Test]
    public function authenticated_user_can_list_products(): void
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson(route('products.index'));

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'category_id',
                        'category',
                        'supplier_id',
                        'supplier',
                        'name',
                        'sku',
                        'description',
                        'price',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    #[Test]
    public function authenticated_user_can_view_single_product(): void
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

        $response = $this->getJson(
            route('products.show', $product)
        );

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'category_id' => $category->id,
                    'supplier_id' => $supplier->id,
                    'category' => [
                        'id' => $category->id,
                    ],
                    'supplier' => [
                        'id' => $supplier->id,
                    ],
                ],
            ]);
    }

    #[Test]
    public function authenticated_user_can_update_product(): void
    {
        $oldCategory = Category::factory()->create();
        $newCategory = Category::factory()->create();

        $oldSupplier = Supplier::factory()->create();
        $newSupplier = Supplier::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $oldCategory->id,
            'supplier_id' => $oldSupplier->id,
        ]);

        $response = $this->putJson(
            route('products.update', $product),
            [
                'category_id' => $newCategory->id,
                'supplier_id' => $newSupplier->id,
                'name' => 'Updated Product',
                'sku' => $product->sku,
                'description' => 'Updated Description',
                'price' => 500,
            ]
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Product updated successfully.',
                'data' => [
                    'category_id' => $newCategory->id,
                    'supplier_id' => $newSupplier->id,
                    'category' => [
                        'id' => $newCategory->id,
                    ],
                    'supplier' => [
                        'id' => $newSupplier->id,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'category_id' => $newCategory->id,
            'supplier_id' => $newSupplier->id,
            'name' => 'Updated Product',
        ]);
    }

    #[Test]
    public function authenticated_user_can_soft_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson(
            route('products.destroy', $product)
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Product deleted successfully.',
            ]);

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }

    #[Test]
    public function authenticated_user_can_restore_product(): void
    {
        $product = Product::factory()->create();

        $product->delete();

        $response = $this->patchJson(
            route('products.restore', $product->id)
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Product restored successfully.',
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'deleted_at' => null,
        ]);
    }

    #[Test]
    public function product_requires_existing_category(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->postJson(route('products.store'), [
            'category_id' => 999999,
            'supplier_id' => $supplier->id,
            'name' => 'Laptop',
            'sku' => 'LAPTOP-001',
            'description' => 'Gaming Laptop',
            'price' => 50000,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('category_id');
    }

    #[Test]
    public function product_requires_existing_supplier(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson(route('products.store'), [
            'category_id' => $category->id,
            'supplier_id' => 999999,
            'name' => 'Laptop',
            'sku' => 'LAPTOP-002',
            'description' => 'Gaming Laptop',
            'price' => 50000,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('supplier_id');
    }
}