<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

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
        $data = [
            'name' => 'iPhone 16',
            'sku' => 'IPHONE-16',
            'description' => 'Latest iPhone',
            'price' => 99999,
            'stock' => 25,
        ];

        $response = $this->postJson(route('products.store'), $data);

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Product created successfully.',
            ]);

        $this->assertDatabaseHas('products', [
            'sku' => 'IPHONE-16',
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
                'data',
                'links',
                'meta',
            ]);
    }

    #[Test]
    public function authenticated_user_can_view_single_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson(route('products.show', $product->id));

        $response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $product->id,
            ]);
    }

    #[Test]
    public function authenticated_user_can_update_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->putJson(route('products.update', $product->id), [
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
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
        ]);
    }

    #[Test]
    public function authenticated_user_can_soft_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson( route('products.destroy', $product->id));

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
}