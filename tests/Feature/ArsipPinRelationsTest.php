<?php

namespace Tests\Feature;

use App\Models\Arsip;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ArsipPinRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_creates_pivot_table_with_required_columns_and_constraints(): void
    {
        $this->assertTrue(Schema::hasTable('arsip_pin'));
        $this->assertTrue(Schema::hasColumns('arsip_pin', ['user_id', 'arsip_id', 'created_at']));
        $this->assertFalse(Schema::hasColumn('arsip_pin', 'updated_at'));

        $foreignKeys = DB::select("PRAGMA foreign_key_list('arsip_pin')");
        $referensi = collect($foreignKeys)->mapWithKeys(fn ($foreignKey) => [
            $foreignKey->from => [$foreignKey->table, $foreignKey->on_delete],
        ]);

        $this->assertSame(['users', 'CASCADE'], $referensi['user_id']);
        $this->assertSame(['arsips', 'CASCADE'], $referensi['arsip_id']);
    }

    public function test_user_and_archive_relations_use_the_expected_pivot_keys(): void
    {
        $user = new User;
        $arsip = new Arsip;

        $this->assertInstanceOf(BelongsToMany::class, $user->pins());
        $this->assertSame('arsip_pin', $user->pins()->getTable());
        $this->assertSame('user_id', $user->pins()->getForeignPivotKeyName());
        $this->assertSame('arsip_id', $user->pins()->getRelatedPivotKeyName());

        $this->assertInstanceOf(BelongsToMany::class, $arsip->dipinOleh());
        $this->assertSame('arsip_pin', $arsip->dipinOleh()->getTable());
        $this->assertSame('arsip_id', $arsip->dipinOleh()->getForeignPivotKeyName());
        $this->assertSame('user_id', $arsip->dipinOleh()->getRelatedPivotKeyName());
    }

    public function test_user_and_archive_relations_return_each_other(): void
    {
        $user = $this->buatUser('pemilik');
        $arsip = $this->buatArsip($user);
        $user->pins()->attach($arsip->id);

        $this->assertTrue($user->pins()->whereKey($arsip->id)->exists());
        $this->assertTrue($arsip->dipinOleh()->whereKey($user->id)->exists());
    }

    public function test_database_rejects_duplicate_pin_for_same_user_and_archive(): void
    {
        $user = $this->buatUser('pemilik');
        $arsip = $this->buatArsip($user);
        $user->pins()->attach($arsip->id);

        $this->expectException(QueryException::class);
        $user->pins()->attach($arsip->id);
    }

    public function test_force_deleting_archive_cascades_to_its_pins(): void
    {
        $user = $this->buatUser('pemilik');
        $arsip = $this->buatArsip($user);
        $user->pins()->attach($arsip->id);

        $arsip->forceDelete();

        $this->assertDatabaseMissing('arsip_pin', ['user_id' => $user->id, 'arsip_id' => $arsip->id]);
    }

    public function test_allowed_account_deletion_cascades_to_its_pins(): void
    {
        $admin = $this->buatUser('admin', 'admin');
        $target = $this->buatUser('target');
        $arsip = $this->buatArsip($admin);
        $target->pins()->attach($arsip->id);

        $this->actingAs($admin)->delete(route('akun.destroy', $target))
            ->assertRedirect(route('akun.index'))
            ->assertSessionHas('success', 'Akun berhasil dihapus.');

        $this->assertDatabaseMissing('arsip_pin', ['user_id' => $target->id, 'arsip_id' => $arsip->id]);
    }

    public function test_soft_deleting_archive_preserves_its_pins(): void
    {
        $user = $this->buatUser('pemilik');
        $arsip = $this->buatArsip($user);
        $user->pins()->attach($arsip->id);

        $arsip->delete();

        $this->assertDatabaseHas('arsip_pin', ['user_id' => $user->id, 'arsip_id' => $arsip->id]);
        $this->assertTrue($user->pins()->withTrashed()->whereKey($arsip->id)->exists());
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
