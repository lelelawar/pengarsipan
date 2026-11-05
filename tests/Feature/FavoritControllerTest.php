<?php

namespace Tests\Feature;

use App\Http\Controllers\FavoritController;
use App\Models\ActivityLog;
use App\Models\Arsip;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class FavoritControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_favorites_or_pin_or_unpin(): void
    {
        $arsip = $this->buatArsip($this->buatUser('pemilik'));

        $this->get(route('favorit.index'))->assertRedirect(route('login'));
        $this->from(route('arsip.index'))->post(route('arsip.pin', $arsip))->assertRedirect(route('login'));
        $this->from(route('arsip.index'))->delete(route('arsip.unpin', $arsip))->assertRedirect(route('login'));
    }

    public function test_admin_and_user_can_manage_separate_private_favorites(): void
    {
        $admin = $this->buatUser('admin', 'admin');
        $user = $this->buatUser('user');
        $arsip = $this->buatArsip($user);

        $this->actingAs($admin)->from(route('arsip.index'))->post(route('arsip.pin', $arsip))
            ->assertRedirect(route('arsip.index'));
        $this->actingAs($user)->from(route('arsip.index'))->post(route('arsip.pin', $arsip))
            ->assertRedirect(route('arsip.index'));

        $this->assertDatabaseHas('arsip_pin', ['user_id' => $admin->id, 'arsip_id' => $arsip->id]);
        $this->assertDatabaseHas('arsip_pin', ['user_id' => $user->id, 'arsip_id' => $arsip->id]);

        $dataAdmin = $this->dataFavorit($admin);
        $dataUser = $this->dataFavorit($user);
        $this->assertSame([$arsip->id], $dataAdmin->getCollection()->pluck('id')->all());
        $this->assertSame([$arsip->id], $dataUser->getCollection()->pluck('id')->all());
        $this->assertTrue($dataAdmin->first()->relationLoaded('kategori'));
        $this->assertTrue($dataAdmin->first()->relationLoaded('pembuat'));
    }

    public function test_request_cannot_choose_another_user_as_pin_owner(): void
    {
        $pemilik = $this->buatUser('pemilik');
        $penyerang = $this->buatUser('penyerang');
        $arsip = $this->buatArsip($pemilik);

        $this->actingAs($penyerang)->from(route('arsip.index'))
            ->post(route('arsip.pin', $arsip), ['user_id' => $pemilik->id])
            ->assertRedirect(route('arsip.index'));

        $this->assertDatabaseHas('arsip_pin', ['user_id' => $penyerang->id, 'arsip_id' => $arsip->id]);
        $this->assertDatabaseMissing('arsip_pin', ['user_id' => $pemilik->id, 'arsip_id' => $arsip->id]);
    }

    public function test_repeated_pin_requests_are_idempotent(): void
    {
        $user = $this->buatUser('pemilik');
        $arsip = $this->buatArsip($user);

        $this->actingAs($user)->from(route('arsip.index'))->post(route('arsip.pin', $arsip));
        $this->post(route('arsip.pin', $arsip))->assertRedirect();

        $this->assertSame(1, $user->pins()->whereKey($arsip->id)->count());
    }

    public function test_unpin_only_removes_current_users_pin_and_missing_pin_is_safe(): void
    {
        $pemilik = $this->buatUser('pemilik');
        $lain = $this->buatUser('lain');
        $arsip = $this->buatArsip($pemilik);
        $pemilik->pins()->attach($arsip->id);

        $this->actingAs($lain)->from(route('arsip.index'))
            ->delete(route('arsip.unpin', $arsip), ['user_id' => $pemilik->id])
            ->assertRedirect(route('arsip.index'));
        $this->assertDatabaseHas('arsip_pin', ['user_id' => $pemilik->id, 'arsip_id' => $arsip->id]);

        $this->actingAs($pemilik)->delete(route('arsip.unpin', $arsip))->assertRedirect();
        $this->delete(route('arsip.unpin', $arsip))->assertRedirect();
        $this->assertDatabaseMissing('arsip_pin', ['user_id' => $pemilik->id, 'arsip_id' => $arsip->id]);
    }

    public function test_soft_deleted_archive_cannot_be_pinned_and_is_excluded_from_favorites(): void
    {
        $user = $this->buatUser('pemilik');
        $arsip = $this->buatArsip($user);
        $user->pins()->attach($arsip->id);
        $arsip->delete();

        $this->actingAs($user)->post(route('arsip.pin', $arsip))->assertNotFound();
        $this->assertSame([], $this->dataFavorit($user)->getCollection()->pluck('id')->all());
        $this->assertDatabaseHas('arsip_pin', ['user_id' => $user->id, 'arsip_id' => $arsip->id]);

        $arsip->restore();
        $this->assertSame([$arsip->id], $this->dataFavorit($user)->getCollection()->pluck('id')->all());
    }

    public function test_favoriting_does_not_change_existing_archive_edit_or_delete_permissions(): void
    {
        $pemilik = $this->buatUser('pemilik');
        $lain = $this->buatUser('lain');
        $arsip = $this->buatArsip($pemilik);
        $lain->pins()->attach($arsip->id);

        $this->actingAs($lain)->get(route('arsip.edit', $arsip))->assertForbidden();
        $this->delete(route('arsip.destroy', $arsip))->assertForbidden();
        $this->assertDatabaseHas('arsips', ['id' => $arsip->id, 'deleted_at' => null]);
    }

    public function test_pin_and_unpin_do_not_create_activity_logs(): void
    {
        $user = $this->buatUser('pemilik');
        $arsip = $this->buatArsip($user);
        $jumlahLog = ActivityLog::count();

        $this->actingAs($user)->from(route('arsip.index'))->post(route('arsip.pin', $arsip));
        $this->delete(route('arsip.unpin', $arsip));

        $this->assertSame($jumlahLog, ActivityLog::count());
    }

    private function dataFavorit(User $user): LengthAwarePaginator
    {
        $this->actingAs($user);
        $request = Request::create('/favorit', 'GET');
        $request->setUserResolver(fn () => $user);
        $dataFavorit = null;
        $factory = Mockery::mock(Factory::class);
        $factory->shouldReceive('make')
            ->once()
            ->with('favorit.index', Mockery::on(function ($data) use (&$dataFavorit) {
                $dataFavorit = $data['arsips'];

                return true;
            }), [])
            ->andReturnNull();
        $this->app->instance('view', $factory);

        app(FavoritController::class)->index($request);

        return $dataFavorit;
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

    private function buatArsip(User $pemilik): Arsip
    {
        $kategori = Kategori::create(['nama' => 'Kategori '.$pemilik->username]);

        return Arsip::create([
            'nomor_arsip' => 'ARS-'.$pemilik->username,
            'judul' => 'Arsip Uji',
            'kategori_id' => $kategori->id,
            'tanggal_arsip' => '2026-11-05',
            'created_by' => $pemilik->id,
        ]);
    }
}
