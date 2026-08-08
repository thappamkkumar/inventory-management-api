<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WarehouseTest extends TestCase
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
    public function authenticated_user_can_create_warehouse(): void
    {
        $response = $this->postJson(route('warehouses.store'), [
            'name' => 'Main Warehouse',
            'code' => 'WH-MAIN',
            'address' => '123 Main Street',
            'city' => 'Pathankot',
            'state' => 'Punjab',
            'country' => 'India',
            'postal_code' => '145001',
            'contact_person' => 'Rahul Sharma',
            'phone' => '9876543210',
            'email' => 'warehouse@example.com',
            'is_active' => true,
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Warehouse created successfully.',
            ]);

        $this->assertDatabaseHas('warehouses', [
            'name' => 'Main Warehouse',
            'code' => 'WH-MAIN',
        ]);
    }

    #[Test]
    public function authenticated_user_can_list_warehouses(): void
    {
        Warehouse::factory()->count(5)->create();

        $response = $this->getJson(route('warehouses.index'));

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    #[Test]
    public function authenticated_user_can_search_warehouses(): void
    {
        Warehouse::factory()->create([
            'name' => 'Main Warehouse',
        ]);

        Warehouse::factory()->create([
            'name' => 'Secondary Warehouse',
        ]);

        $response = $this->getJson(
            route('warehouses.index', [
                'search' => 'Main',
            ])
        );

        $response
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Main Warehouse',
            ])
            ->assertJsonMissing([
                'name' => 'Secondary Warehouse',
            ]);
    }

    #[Test]
    public function authenticated_user_can_filter_warehouses_by_status(): void
    {
        Warehouse::factory()->create([
            'name' => 'Active Warehouse',
            'is_active' => true,
        ]);

        Warehouse::factory()->create([
            'name' => 'Inactive Warehouse',
            'is_active' => false,
        ]);

        $response = $this->getJson(
            route('warehouses.index', [
                'is_active' => true,
            ])
        );

        $response
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Active Warehouse',
            ])
            ->assertJsonMissing([
                'name' => 'Inactive Warehouse',
            ]);
    }

    #[Test]
    public function authenticated_user_can_view_single_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create();

        $response = $this->getJson(
            route('warehouses.show', $warehouse)
        );

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'code' => $warehouse->code,
                ],
            ]);
    }

    #[Test]
    public function authenticated_user_can_update_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create();

        $response = $this->putJson(
            route('warehouses.update', $warehouse),
            [
                'name' => 'Updated Warehouse',
                'code' => $warehouse->code,
                'address' => 'Updated Address',
                'city' => 'Pathankot',
                'state' => 'Punjab',
                'country' => 'India',
                'postal_code' => '145002',
                'contact_person' => 'Amit Sharma',
                'phone' => '9999999999',
                'email' => $warehouse->email,
                'is_active' => true,
            ]
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Warehouse updated successfully.',
            ]);

        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'name' => 'Updated Warehouse',
        ]);
    }

    #[Test]
    public function authenticated_user_can_soft_delete_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create();

        $response = $this->deleteJson(
            route('warehouses.destroy', $warehouse)
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Warehouse deleted successfully.',
            ]);

        $this->assertSoftDeleted('warehouses', [
            'id' => $warehouse->id,
        ]);
    }

    #[Test]
    public function authenticated_user_can_restore_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create();

        $warehouse->delete();

        $response = $this->patchJson(
            route('warehouses.restore', $warehouse->id)
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Warehouse restored successfully.',
            ]);

        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'deleted_at' => null,
        ]);
    }
}