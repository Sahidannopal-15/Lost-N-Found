<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\Paginator;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_displays_the_catalog_and_its_empty_state(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertViewIs('items.index')
            ->assertSee('Belum ada laporan.')
            ->assertSee('Laporkan barang hilang atau temuan pertama.');
    }

    public function test_home_renders_active_items_with_the_ticket_partial(): void
    {
        $item = $this->createItem(['title' => 'KTM hitam', 'type' => 'temuan']);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('KTM hitam')
            ->assertSee('LNF-'.str_pad((string) $item->id, 4, '0', STR_PAD_LEFT))
            ->assertSee('Pos Satpam');
    }

    public function test_catalog_filters_are_present_and_load_results_through_ajax(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('id="filter-katalog"', false)
            ->assertSee('name="q"', false)
            ->assertSee('name="type"', false)
            ->assertSee('name="category_id"', false)
            ->assertSee('name="location_id"', false)
            ->assertSee('name="status"', false)
            ->assertSee('name="date_from"', false)
            ->assertSee('$.getJSON', false);
    }

    public function test_item_detail_route_shows_public_item_information(): void
    {
        $item = $this->createItem([
            'title' => 'Payung lipat',
            'description' => 'Payung hitam ditemukan di ruang baca.',
        ]);

        $this->get('/items/'.$item->id)
            ->assertOk()
            ->assertViewIs('items.show')
            ->assertSee('Payung lipat')
            ->assertSee('Payung hitam ditemukan di ruang baca.')
            ->assertSee($item->category->name)
            ->assertSee($item->location->name)
            ->assertSee($item->code);
    }

    public function test_server_side_catalog_filters_work_without_javascript(): void
    {
        $this->createItem(['title' => 'KTM hijau', 'type' => 'temuan']);
        $this->createItem(['title' => 'Tumbler kuning']);

        $this->get('/?q=KTM&type=temuan')
            ->assertOk()
            ->assertSee('KTM hijau')
            ->assertDontSee('Tumbler kuning');
    }

    public function test_items_api_returns_nine_active_or_claimed_items_per_page(): void
    {
        for ($index = 1; $index <= 9; $index++) {
            $this->createItem(['title' => 'Barang aktif '.$index]);
        }

        $this->createItem(['title' => 'Barang dalam klaim', 'status' => 'dalam_klaim']);
        $this->createItem(['title' => 'Barang selesai', 'status' => 'selesai']);

        $response = $this->getJson('/api/items');

        $response->assertOk()
            ->assertJsonCount(9, 'data')
            ->assertJsonPath('per_page', 9)
            ->assertJsonPath('total', 10)
            ->assertJsonMissingPath('data.0.email')
            ->assertJsonMissingPath('data.0.password');
    }

    public function test_items_api_applies_keyword_and_status_filters(): void
    {
        $item = $this->createItem(['title' => 'KTM biru', 'type' => 'temuan']);
        $this->createItem(['title' => 'Tumbler merah']);

        $query = http_build_query([
            'q' => 'KTM',
            'type' => 'temuan',
            'category_id' => $item->category_id,
            'location_id' => $item->location_id,
            'status' => 'aktif',
            'date_from' => today()->toDateString(),
        ]);
        $response = $this->getJson('/api/items?'.$query);

        $response->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.title', 'KTM biru');
    }

    public function test_item_api_returns_public_details(): void
    {
        $item = $this->createItem(['title' => 'Kunci motor']);

        $this->getJson('/api/items/'.$item->id)
            ->assertOk()
            ->assertJsonPath('id', $item->id)
            ->assertJsonPath('code', $item->code)
            ->assertJsonPath('title', 'Kunci motor')
            ->assertJsonPath('category', $item->category->name)
            ->assertJsonPath('location', $item->location->name)
            ->assertJsonPath('status', 'aktif')
            ->assertJsonMissingPath('email')
            ->assertJsonMissingPath('password');
    }

    public function test_missing_item_api_response_is_json(): void
    {
        $this->getJson('/api/items/999')
            ->assertNotFound()
            ->assertJsonPath('message', 'Barang tidak ditemukan.');
    }

    public function test_items_api_returns_a_nine_item_paginated_list(): void
    {
        $this->getJson('/api/items')
            ->assertOk()
            ->assertJsonPath('data', [])
            ->assertJsonPath('per_page', 9);
    }

    public function test_pagination_uses_bootstrap_five(): void
    {
        $this->assertSame('pagination::bootstrap-5', Paginator::$defaultView);
        $this->assertSame('pagination::simple-bootstrap-5', Paginator::$defaultSimpleView);
    }

    private function createItem(array $attributes = []): Item
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Kategori '.$user->id]);
        $location = Location::create(['name' => 'Pos Satpam '.$user->id]);

        return Item::create(array_merge([
            'user_id' => $user->id,
            'type' => 'hilang',
            'title' => 'Barang contoh',
            'description' => 'Deskripsi barang yang cukup jelas untuk katalog.',
            'category_id' => $category->id,
            'location_id' => $location->id,
            'event_date' => today()->toDateString(),
            'photo_path' => null,
            'status' => 'aktif',
        ], $attributes));
    }
}
