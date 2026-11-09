<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Claim;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessAndAdminTest extends TestCase
{
    use RefreshDatabase;

    private function makeItem(User $owner, array $attributes = []): Item
    {
        $category = Category::create(['name' => 'Kategori '.$owner->id]);
        $location = Location::create(['name' => 'Pos '.$owner->id]);

        return Item::create(array_merge([
            'user_id' => $owner->id,
            'type' => 'temuan',
            'title' => 'Barang contoh',
            'description' => 'Deskripsi barang yang cukup jelas.',
            'category_id' => $category->id,
            'location_id' => $location->id,
            'event_date' => now()->toDateString(),
            'photo_path' => null,
            'status' => 'aktif',
        ], $attributes));
    }

    public function test_disabled_user_session_is_invalidated(): void
    {
        $user = User::factory()->create(['role' => 'pengguna', 'is_active' => true]);
        $this->actingAs($user);

        $user->update(['is_active' => false]);

        $this->get('/riwayat')->assertRedirect('/login');
    }

    public function test_claims_only_for_pengguna(): void
    {
        $owner = User::factory()->create(['role' => 'pengguna']);
        $item = $this->makeItem($owner);

        $petugas = User::factory()->create(['role' => 'petugas']);
        $this->actingAs($petugas)
            ->get(route('claims.create', $item))
            ->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->post(route('claims.store', $item), [
                'description' => str_repeat('a', 20),
                'proof' => str_repeat('b', 10),
            ])
            ->assertForbidden();
    }

    public function test_claim_button_hidden_for_non_pengguna(): void
    {
        $owner = User::factory()->create(['role' => 'pengguna']);
        $item = $this->makeItem($owner);

        $petugas = User::factory()->create(['role' => 'petugas']);
        $this->actingAs($petugas)
            ->get(route('items.show', $item))
            ->assertDontSee('Ajukan klaim');
    }

    public function test_toggle_user_by_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $user = User::factory()->create(['role' => 'pengguna', 'is_active' => true]);

        $this->actingAs($admin)
            ->post(route('admin.users.toggle', $user))
            ->assertSessionHas('sukses');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => false]);
    }

    public function test_toggle_user_forbidden_for_non_admin(): void
    {
        $pengguna = User::factory()->create(['role' => 'pengguna', 'is_active' => true]);
        $target = User::factory()->create(['role' => 'pengguna', 'is_active' => true]);

        $this->actingAs($pengguna)
            ->post(route('admin.users.toggle', $target))
            ->assertForbidden();
    }

    public function test_admin_can_delete_item_including_claimed(): void
    {
        $owner = User::factory()->create(['role' => 'pengguna']);
        $item = $this->makeItem($owner, ['status' => 'dalam_klaim']);
        $claimer = User::factory()->create(['role' => 'pengguna']);
        Claim::create([
            'item_id' => $item->id,
            'user_id' => $claimer->id,
            'description' => str_repeat('c', 20),
            'proof' => str_repeat('d', 10),
            'status' => 'menunggu',
        ]);

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)
            ->delete(route('items.destroy', $item))
            ->assertRedirect(route('items.index'));

        $this->assertDatabaseMissing('items', ['id' => $item->id]);
        $this->assertDatabaseMissing('claims', ['item_id' => $item->id]);
    }

    public function test_admin_can_view_claims_read_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->get(route('petugas.claims.index'))
            ->assertOk()
            ->assertDontSee('Setujui dan serahkan')
            ->assertDontSee('Tolak klaim');
    }

    public function test_admin_cannot_approve_claim(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $owner = User::factory()->create(['role' => 'pengguna']);
        $item = $this->makeItem($owner);
        $claim = Claim::create([
            'item_id' => $item->id,
            'user_id' => User::factory()->create(['role' => 'pengguna'])->id,
            'description' => str_repeat('c', 20),
            'proof' => str_repeat('d', 10),
            'status' => 'menunggu',
        ]);

        $this->actingAs($admin)
            ->post(route('petugas.claims.approve', $claim))
            ->assertForbidden();
    }

    public function test_petugas_cannot_process_own_claim(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas', 'is_active' => true]);
        $owner = User::factory()->create(['role' => 'pengguna']);
        $item = $this->makeItem($owner);
        $claim = Claim::create([
            'item_id' => $item->id,
            'user_id' => $petugas->id,
            'description' => str_repeat('c', 20),
            'proof' => str_repeat('d', 10),
            'status' => 'menunggu',
        ]);

        $this->actingAs($petugas)
            ->post(route('petugas.claims.approve', $claim))
            ->assertSessionHas('galat', 'Tidak dapat memproses klaim milik sendiri.');

        $this->actingAs($petugas)
            ->post(route('petugas.claims.reject', $claim), ['officer_note' => 'catatan'])
            ->assertSessionHas('galat', 'Tidak dapat memproses klaim milik sendiri.');
    }

    public function test_dashboard_admin_ok_and_numbers(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Total laporan')
            ->assertSee('Total pengguna');
    }

    public function test_dashboard_forbidden_for_pengguna(): void
    {
        $pengguna = User::factory()->create(['role' => 'pengguna', 'is_active' => true]);

        $this->actingAs($pengguna)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_dashboard_guest_redirects_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect('/login');
    }
}