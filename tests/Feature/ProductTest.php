<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
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

        $response = $this->postJson(route('products.store'), [
            'category_id' => $category->id,
            'name' => 'iPhone 16',
            'sku' => 'IPHONE-16',
            'description' => 'Latest iPhone',
            'price' => 99999,
            'stock' => 25,
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Product created successfully.',
                'data' => [
                    'category_id' => $category->id,
                    'category' => [
                        'id' => $category->id,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'sku' => 'IPHONE-16',
            'category_id' => $category->id,
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
                        'name',
                        'sku',
                        'description',
                        'price',
                        'stock',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    #[Test]
    public function authenticated_user_can_view_single_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson(route('products.show', $product));

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'category_id' => $product->category_id,
                    'category' => [
                        'id' => $product->category_id,
                    ],
                ],
            ]);
    }

    #[Test]
    public function authenticated_user_can_update_product(): void
    {
        $oldCategory = Category::factory()->create();
        $newCategory = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $oldCategory->id,
        ]);

        $response = $this->putJson(route('products.update', $product), [
            'category_id' => $newCategory->id,
            'name' => 'Updated Product',
            'sku' => $product->sku,
            'description' => 'Updated Description',
            'price' => 500,
            'stock' => 10,
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Product updated successfully.',
                'data' => [
                    'category_id' => $newCategory->id,
                    'category' => [
                        'id' => $newCategory->id,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'category_id' => $newCategory->id,
            'name' => 'Updated Product',
        ]);
    }

    #[Test]
    public function authenticated_user_can_soft_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson(route('products.destroy', $product));

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

        $response = $this->patchJson(route('products.restore', $product->id));

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
        $response = $this->postJson(route('products.store'), [
            'category_id' => 999999,
            'name' => 'Laptop',
            'sku' => 'LAPTOP-001',
            'description' => 'Gaming Laptop',
            'price' => 1200,
            'stock' => 5,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('category_id');
    }
}