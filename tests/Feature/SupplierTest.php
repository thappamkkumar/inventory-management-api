<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Models\Product;


class SupplierTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(
            User::factory()->create()
        );
    }

    #[Test]
    public function authenticated_user_can_create_supplier(): void
    {
        $response = $this->postJson(route('suppliers.store'), [
            'name' => 'ABC Electronics',
            'email' => 'contact@abc.com',
            'phone' => '9876543210',
            'contact_person' => 'Rahul Sharma',
            'address' => '123 Main Street',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'country' => 'India',
            'postal_code' => '110001',
            'is_active' => true,
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Supplier created successfully.',
            ]);

        $this->assertDatabaseHas('suppliers', [
            'name' => 'ABC Electronics',
            'email' => 'contact@abc.com',
        ]);
    }

    #[Test]
    public function authenticated_user_can_list_suppliers(): void
    {
        Supplier::factory()->count(5)->create();

        $response = $this->getJson(route('suppliers.index'));

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    #[Test]
    public function authenticated_user_can_search_suppliers(): void
    {
        Supplier::factory()->create([
            'name' => 'ABC Electronics',
        ]);

        Supplier::factory()->create([
            'name' => 'XYZ Furniture',
        ]);

        $response = $this->getJson(
            route('suppliers.index', [
                'search' => 'ABC',
            ])
        );

        $response
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'ABC Electronics',
            ])
            ->assertJsonMissing([
                'name' => 'XYZ Furniture',
            ]);
    }

    #[Test]
    public function authenticated_user_can_filter_suppliers_by_status(): void
    {
        Supplier::factory()->create([
            'name' => 'Active Supplier',
            'is_active' => true,
        ]);

        Supplier::factory()->create([
            'name' => 'Inactive Supplier',
            'is_active' => false,
        ]);

        $response = $this->getJson(
            route('suppliers.index', [
                'is_active' => true,
            ])
        );

        $response
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Active Supplier',
            ])
            ->assertJsonMissing([
                'name' => 'Inactive Supplier',
            ]);
    }

    #[Test]
    public function authenticated_user_can_view_single_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->getJson(
            route('suppliers.show', $supplier)
        );

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'email' => $supplier->email,
                ],
            ]);
    }

    #[Test]
    public function authenticated_user_can_update_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->putJson(
            route('suppliers.update', $supplier),
            [
                'name' => 'Updated Supplier',
                'email' => $supplier->email,
                'phone' => '9999999999',
                'contact_person' => 'Amit Sharma',
                'address' => 'Updated Address',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'country' => 'India',
                'postal_code' => '110002',
                'is_active' => true,
            ]
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Supplier updated successfully.',
            ]);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Updated Supplier',
        ]);
    }

    #[Test]
    public function authenticated_user_can_soft_delete_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->deleteJson(
            route('suppliers.destroy', $supplier)
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Supplier deleted successfully.',
            ]);

        $this->assertSoftDeleted('suppliers', [
            'id' => $supplier->id,
        ]);
    }

    #[Test]
    public function authenticated_user_can_restore_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        $supplier->delete();

        $response = $this->patchJson(
            route('suppliers.restore', $supplier->id)
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Supplier restored successfully.',
            ]);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'deleted_at' => null,
        ]);
    }

    #[Test]
    public function supplier_has_products(): void
    {
        $supplier = Supplier::factory()->create();

        Product::factory()
            ->count(3)
            ->create([
                'supplier_id' => $supplier->id,
            ]);

        $supplier->load('products');

        $this->assertCount(3, $supplier->products);
    }

    #[Test]
    public function supplier_products_belong_to_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        $product = Product::factory()->create([
            'supplier_id' => $supplier->id,
        ]);

        $this->assertTrue(
            $product->supplier->is($supplier)
        );
    }


}