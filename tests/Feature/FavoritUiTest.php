<?php

namespace Tests\Feature;

use App\Models\Arsip;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoritUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_favorites_page_renders_only_the_authenticated_users_archives(): void
    {
        $admin = $this->buatUser('admin', 'admin');
        $user = $this->buatUser('user');
        $arsipAdmin = $this->buatArsip($admin, 'ADMIN');
        $arsipUser = $this->buatArsip($user, 'USER');
        $admin->pins()->attach($arsipAdmin->id);
        $user->pins()->attach($arsipUser->id);

        $this->actingAs($admin)->get(route('favorit.index'))
            ->assertOk()
            ->assertSee('Arsip ADMIN')
            ->assertDontSee('Arsip USER');

        $this->actingAs($user)->get(route('favorit.index'))
            ->assertOk()
            ->assertSee('Arsip USER')
            ->assertDontSee('Arsip ADMIN');
    }

    public function test_favorites_page_shows_an_informative_empty_state(): void
    {
        $user = $this->buatUser('user');

        $this->actingAs($user)->get(route('favorit.index'))
            ->assertOk()
            ->assertSee('Belum ada Favorit.')
            ->assertSee('Pin arsip dari daftar atau halaman detail untuk menyimpannya di sini.')
            ->assertSee(route('arsip.index'));
    }

    public function test_archive_list_pin_buttons_use_post_or_delete_based_on_pivot(): void
    {
        $user = $this->buatUser('user');
        $dipin = $this->buatArsip($user, 'DIPIN');
        $belumDipin = $this->buatArsip($user, 'BELUM');
        $user->pins()->attach($dipin->id);

        $this->actingAs($user)->get(route('arsip.index'))
            ->assertOk()
            ->assertSee(route('arsip.unpin', $dipin))
            ->assertSee('value="DELETE"', false)
            ->assertSee(route('arsip.pin', $belumDipin))
            ->assertSee('Pin ke Favorit')
            ->assertSee('Unpin');
    }

    public function test_archive_detail_pin_button_uses_post_or_delete_based_on_pivot(): void
    {
        $user = $this->buatUser('user');
        $arsip = $this->buatArsip($user, 'DETAIL');

        $this->actingAs($user)->get(route('arsip.show', $arsip))
            ->assertOk()
            ->assertSee(route('arsip.pin', $arsip))
            ->assertSee('Pin ke Favorit');

        $user->pins()->attach($arsip->id);

        $this->get(route('arsip.show', $arsip))
            ->assertOk()
            ->assertSee(route('arsip.unpin', $arsip))
            ->assertSee('value="DELETE"', false)
            ->assertSee('Unpin');
    }

    public function test_favorites_pagination_and_search_work(): void
    {
        $user = $this->buatUser('user');
        $arsipTerakhir = null;

        for ($nomor = 1; $nomor <= 12; $nomor++) {
            $arsip = $this->buatArsip($user, str_pad((string) $nomor, 2, '0', STR_PAD_LEFT));
            $user->pins()->attach($arsip->id, ['created_at' => now()->addSeconds($nomor)]);
            $arsipTerakhir = $arsip;
        }

        $this->actingAs($user)->get(route('favorit.index'))
            ->assertOk()
            ->assertSee('page=2')
            ->assertSee('Arsip 12');

        $this->get(route('favorit.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Arsip 02')
            ->assertDontSee('Arsip 12');

        $this->get(route('favorit.index', ['q' => $arsipTerakhir->nomor_arsip]))
            ->assertOk()
            ->assertSee('Arsip 12')
            ->assertDontSee('Arsip 01');
    }

    private function buatUser(string $username, string $role = 'user'): User
    {
        return User::create([
            'nama' => ucfirst($username),
            'username' => $username,
            'password' => 'password123',
            'role' => $role,
        ]);
    }

    private function buatArsip(User $pemilik, string $kode): Arsip
    {
        $kategori = Kategori::create(['nama' => 'Kategori '.$pemilik->username.' '.$kode]);

        return Arsip::create([
            'nomor_arsip' => 'ARS-'.$kode,
            'judul' => 'Arsip '.$kode,
            'kategori_id' => $kategori->id,
            'tanggal_arsip' => '2026-11-05',
            'created_by' => $pemilik->id,
        ]);
    }
}
