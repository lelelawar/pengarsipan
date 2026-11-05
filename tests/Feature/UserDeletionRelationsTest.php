<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Arsip;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDeletionRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_relations_use_the_expected_foreign_keys(): void
    {
        $user = new User();

        $this->assertInstanceOf(HasMany::class, $user->arsips());
        $this->assertSame('created_by', $user->arsips()->getForeignKeyName());
        $this->assertInstanceOf(HasMany::class, $user->activityLogs());
        $this->assertSame('user_id', $user->activityLogs()->getForeignKeyName());
    }

    public function test_arsips_relation_finds_active_and_soft_deleted_archives(): void
    {
        $user = $this->buatUser('pemilik');
        $arsip = $this->buatArsip($user);

        $this->assertTrue($user->arsips()->exists());

        $arsip->delete();

        $this->assertFalse($user->arsips()->exists());
        $this->assertTrue($user->arsips()->withTrashed()->exists());
    }

    public function test_account_with_active_or_soft_deleted_archive_cannot_be_deleted(): void
    {
        $admin = $this->buatUser('admin', 'admin');
        $target = $this->buatUser('target');
        $arsip = $this->buatArsip($target);

        $this->actingAs($admin)->delete(route('akun.destroy', $target))
            ->assertSessionHas('error', 'Akun ini sudah punya arsip atau riwayat aktivitas, jadi tidak bisa dihapus.');
        $this->assertDatabaseHas('users', ['id' => $target->id]);

        $arsip->delete();

        $this->actingAs($admin)->delete(route('akun.destroy', $target))
            ->assertSessionHas('error', 'Akun ini sudah punya arsip atau riwayat aktivitas, jadi tidak bisa dihapus.');
        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }

    public function test_account_with_activity_log_cannot_be_deleted(): void
    {
        $admin = $this->buatUser('admin', 'admin');
        $target = $this->buatUser('target');
        ActivityLog::create([
            'user_id' => $target->id,
            'aksi' => 'tambah',
            'deskripsi' => 'Menambah arsip uji',
        ]);

        $this->actingAs($admin)->delete(route('akun.destroy', $target))
            ->assertSessionHas('error', 'Akun ini sudah punya arsip atau riwayat aktivitas, jadi tidak bisa dihapus.');
        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }

    public function test_account_without_archives_or_logs_can_be_deleted(): void
    {
        $admin = $this->buatUser('admin', 'admin');
        $target = $this->buatUser('target');

        $this->actingAs($admin)->delete(route('akun.destroy', $target))
            ->assertRedirect(route('akun.index'))
            ->assertSessionHas('success', 'Akun berhasil dihapus.');

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'aksi' => 'hapus',
            'deskripsi' => 'Menghapus akun target',
        ]);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = $this->buatUser('admin', 'admin');

        $this->actingAs($admin)->delete(route('akun.destroy', $admin))
            ->assertSessionHas('error', 'Kamu tidak bisa menghapus akunmu sendiri.');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertDatabaseMissing('activity_logs', [
            'user_id' => $admin->id,
            'deskripsi' => 'Menghapus akun admin',
        ]);
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
        $kategori = Kategori::create(['nama' => 'Kategori ' . $pemilik->username]);

        return Arsip::create([
            'nomor_arsip' => 'ARS-' . $pemilik->username,
            'judul' => 'Arsip Uji',
            'kategori_id' => $kategori->id,
            'tanggal_arsip' => '2026-11-05',
            'created_by' => $pemilik->id,
        ]);
    }
}
