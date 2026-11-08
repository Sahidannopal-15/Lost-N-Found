<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Claim;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessRuleTest extends TestCase
{
    use RefreshDatabase;

    private function createItem(array $attributes = []): Item
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Kategori '.$user->id]);
        $location = Location::create(['name' => 'Pos Satpam '.$user->id]);

        return Item::create(array_merge([
            'user_id' => $user->id,
            'type' => 'hilang',
            'title' => 'Barang contoh',
            'description' => 'Deskripsi barang yang cukup jelas.',
            'category_id' => $category->id,
            'location_id' => $location->id,
            'event_date' => now()->toDateString(),
            'photo_path' => null,
            'status' => 'aktif',
        ], $attributes));
    }

    private function createClaim(Item $item, User $user, string $status = 'menunggu'): Claim
    {
        return Claim::create([
            'item_id' => $item->id,
            'user_id' => $user->id,
            'description' => 'Ciri klaim contoh',
            'proof' => 'Bukti contoh',
            'status' => $status,
        ]);
    }

    public function test_petugas_cannot_approve_or_reject_non_waiting_claims()
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $owner = User::factory()->create();
        $item = $this->createItem(['user_id' => $owner->id]);
        $claim = $this->createClaim($item, $owner, 'disetujui');

        $this->actingAs($petugas)
            ->post(route('petugas.claims.approve', $claim))
            ->assertSessionHas('galat', 'Klaim tidak dapat diproses.');

        $claim2 = $this->createClaim($item, $owner, 'ditolak');

        $this->actingAs($petugas)
            ->post(route('petugas.claims.reject', $claim2))
            ->assertSessionHas('galat', 'Klaim tidak dapat diproses.');
    }

    public function test_claim_store_rejects_non_temuan_non_active_owner_and_existing_pending()
    {
        $pengguna = User::factory()->create(['role' => 'pengguna']);

        $nonTemuan = $this->createItem(['type' => 'hilang']);
        $this->actingAs($pengguna)
            ->post(route('claims.store', $nonTemuan), [
                'description' => 'ciri',
                'proof' => 'bukti',
            ])->assertForbidden();

        $nonActive = $this->createItem(['type' => 'temuan', 'status' => 'dalam_klaim']);
        $this->actingAs($pengguna)
            ->post(route('claims.store', $nonActive), [
                'description' => 'ciri',
                'proof' => 'bukti',
            ])->assertForbidden();

        $owner = User::factory()->create(['role' => 'pengguna']);
        $own = $this->createItem(['user_id' => $owner->id]);
        $this->actingAs($owner)
            ->post(route('claims.store', $own), [
                'description' => 'ciri',
                'proof' => 'bukti',
            ])->assertForbidden();

        $claiming = User::factory()->create(['role' => 'pengguna']);
        $item = $this->createItem(['type' => 'temuan']);
        $this->createClaim($item, $claiming, 'menunggu');

        $this->actingAs($claiming)
            ->post(route('claims.store', $item), [
                'description' => 'ciri',
                'proof' => 'bukti',
            ])->assertSessionHas('galat', 'Barang ini sudah dalam proses klaim.');
    }

    public function test_item_update_and_destroy_restrictions()
    {
        $owner = User::factory()->create(['role' => 'pengguna']);
        $other = User::factory()->create(['role' => 'pengguna']);
        $item = $this->createItem(['user_id' => $owner->id]);

        $this->actingAs($other)
            ->put(route('items.update', $item), [])
            ->assertForbidden();

        $this->actingAs($other)
            ->delete(route('items.destroy', $item))
            ->assertForbidden();

        $claimed = $this->createItem(['user_id' => $owner->id, 'status' => 'dalam_klaim']);
        $this->actingAs($owner)
            ->put(route('items.update', $claimed), [])
            ->assertForbidden();

        $this->actingAs($owner)
            ->delete(route('items.destroy', $claimed))
            ->assertForbidden();
    }
}
