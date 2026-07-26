<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): void
    {
        Sanctum::actingAs(User::factory()->create());
    }

    #[Test]
    public function authenticated_user_can_create_category(): void
    {
        $this->authenticate();

        $response = $this->postJson(route('categories.store'), [
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Electronic products',
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Category created successfully.',
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Electronics',
        ]);
    }

    #[Test]
    public function authenticated_user_can_list_categories(): void
    {
        $this->authenticate();

        Category::factory()->count(5)->create();

        $response = $this->getJson(route('categories.index'));

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    #[Test]
    public function authenticated_user_can_view_single_category(): void
    {
        $this->authenticate();

        $category = Category::factory()->create();

        $response = $this->getJson(route('categories.show', $category->id));

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $category->id,
                ],
            ]);
    }

    #[Test]
    public function authenticated_user_can_update_category(): void
    {
        $this->authenticate();

        $category = Category::factory()->create();

        $response = $this->putJson(route('categories.update', $category->id), [
            'name' => 'Updated Category',
            'slug' => 'updated-category',
            'description' => 'Updated description',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Category updated successfully.',
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Updated Category',
        ]);
    }

    #[Test]
    public function authenticated_user_can_soft_delete_category(): void
    {
        $this->authenticate();

        $category = Category::factory()->create();

        $response = $this->deleteJson(route('categories.destroy', $category->id));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Category deleted successfully.',
            ]);

        $this->assertSoftDeleted('categories', [
            'id' => $category->id,
        ]);
    }

    #[Test]
    public function authenticated_user_can_restore_category(): void
    {
        $this->authenticate();

        $category = Category::factory()->create();

        $category->delete();

        $response = $this->patchJson(route('categories.restore', $category->id));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Category restored successfully.',
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'deleted_at' => null,
        ]);
    }
}