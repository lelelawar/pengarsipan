<?php

namespace Tests\Feature;

use App\Models\Arsip;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use LogicException;
use Tests\TestCase;

class PrivateStorageArsipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('arsip_private');
    }

    public function test_new_upload_is_saved_to_private_storage(): void
    {
        $user = $this->buatUser();
        $kategori = Kategori::create(['nama' => 'Surat Masuk']);

        $response = $this->actingAs($user)->post(route('arsip.store'), [
            'nomor_arsip' => 'SM-001',
            'judul' => 'Surat Uji',
            'kategori_id' => $kategori->id,
            'tanggal_arsip' => '2026-11-03',
            'file' => UploadedFile::fake()->createWithContent('surat.pdf', '%PDF-1.4 contoh'),
        ]);

        $response->assertRedirect();
        $arsip = Arsip::firstOrFail();
        $this->assertSame('arsip_private', $arsip->file_disk);
        Storage::disk('arsip_private')->assertExists($arsip->file_path);
        Storage::disk('public')->assertMissing($arsip->file_path);
    }

    public function test_preview_and_download_continue_through_authenticated_controller(): void
    {
        $user = $this->buatUser();
        $admin = $this->buatUser('adminpreview', 'admin');
        $arsip = $this->buatArsip($user, 'private-preview.pdf', 'arsip_private');
        Storage::disk('arsip_private')->put($arsip->file_path, '%PDF-1.4 isi');

        $this->get(route('arsip.preview', $arsip))->assertRedirect(route('login'));
        $this->actingAs($user)->get(route('arsip.preview', $arsip))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->actingAs($user)->get(route('arsip.download', $arsip))->assertOk();
        $this->actingAs($admin)->get(route('arsip.preview', $arsip))->assertOk();
        $this->actingAs($admin)->get(route('arsip.download', $arsip))->assertOk();
    }

    public function test_user_can_still_view_another_users_file_but_cannot_edit_it(): void
    {
        $owner = $this->buatUser('owner');
        $userLain = $this->buatUser('userlain');
        $arsip = $this->buatArsip($owner, 'milik-owner.pdf', 'arsip_private');
        Storage::disk('arsip_private')->put($arsip->file_path, '%PDF-1.4 isi');

        $this->actingAs($userLain)->get(route('arsip.preview', $arsip))->assertOk();
        $this->actingAs($userLain)->get(route('arsip.edit', $arsip))->assertForbidden();
    }

    public function test_replacing_file_uses_private_storage_and_removes_old_file_after_update(): void
    {
        $user = $this->buatUser();
        $arsip = $this->buatArsip($user, 'lama.pdf', 'public');
        Storage::disk('public')->put($arsip->file_path, '%PDF-1.4 lama');

        $response = $this->actingAs($user)->put(route('arsip.update', $arsip), [
            'nomor_arsip' => $arsip->nomor_arsip,
            'judul' => 'Judul Diperbarui',
            'kategori_id' => $arsip->kategori_id,
            'tanggal_arsip' => '2026-11-03',
            'file' => UploadedFile::fake()->createWithContent('baru.pdf', '%PDF-1.4 baru'),
        ]);

        $response->assertRedirect(route('arsip.show', $arsip));
        $arsip->refresh();
        $this->assertSame('arsip_private', $arsip->file_disk);
        Storage::disk('arsip_private')->assertExists($arsip->file_path);
        Storage::disk('public')->assertMissing('arsip/lama.pdf');
    }

    public function test_failed_database_update_keeps_old_file_and_cleans_new_upload(): void
    {
        $user = $this->buatUser();
        $arsip = $this->buatArsip($user, 'lama-gagal.pdf', 'public');
        Storage::disk('public')->put($arsip->file_path, '%PDF-1.4 lama');

        Arsip::updating(function (Arsip $model) use ($arsip) {
            if ($model->is($arsip)) {
                throw new \RuntimeException('Simulasi kegagalan database.');
            }
        });

        try {
            $this->withoutExceptionHandling()->actingAs($user)->put(route('arsip.update', $arsip), [
                'nomor_arsip' => $arsip->nomor_arsip,
                'judul' => 'Perubahan gagal',
                'kategori_id' => $arsip->kategori_id,
                'tanggal_arsip' => '2026-11-03',
                'file' => UploadedFile::fake()->createWithContent('baru.pdf', '%PDF-1.4 baru'),
            ]);
            $this->fail('Update seharusnya gagal pada event model.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulasi kegagalan database.', $e->getMessage());
        }

        $this->assertSame('public', $arsip->fresh()->file_disk);
        Storage::disk('public')->assertExists($arsip->file_path);
        $this->assertSame([], Storage::disk('arsip_private')->allFiles('arsip'));
    }

    public function test_soft_delete_restore_and_force_delete_use_expected_file_lifecycle(): void
    {
        $admin = $this->buatUser('admin', 'admin');
        $arsip = $this->buatArsip($admin, 'sampah.pdf', 'arsip_private');
        Storage::disk('arsip_private')->put($arsip->file_path, '%PDF-1.4 isi');

        $arsip->delete();
        Storage::disk('arsip_private')->assertExists($arsip->file_path);
        $arsip->restore();
        Storage::disk('arsip_private')->assertExists($arsip->file_path);
        $arsip->delete();
        $arsip->forceDelete();
        Storage::disk('arsip_private')->assertMissing($arsip->file_path);
    }

    public function test_migration_command_verifies_then_switches_and_preserves_public_source(): void
    {
        $owner = $this->buatUser();
        $arsip = $this->buatArsip($owner, 'asal.pdf', 'public');
        $isi = '%PDF-1.4 arsip lama';
        Storage::disk('public')->put($arsip->file_path, $isi);

        $this->artisan('arsip:migrasikan-ke-private --dry-run')->assertSuccessful();
        $this->assertSame('public', $arsip->fresh()->file_disk);
        Storage::disk('arsip_private')->assertMissing($arsip->file_path);

        $this->artisan('arsip:migrasikan-ke-private --apply')->assertSuccessful();
        $this->assertSame('arsip_private', $arsip->fresh()->file_disk);
        Storage::disk('public')->assertExists($arsip->file_path);
        Storage::disk('arsip_private')->assertExists($arsip->file_path);
        $this->assertSame(hash('sha256', $isi), hash('sha256', Storage::disk('arsip_private')->get($arsip->file_path)));

        $this->artisan('arsip:migrasikan-ke-private --apply')->assertSuccessful();
        Storage::disk('public')->assertExists($arsip->file_path);
    }

    public function test_rollback_keeps_old_public_source_and_preserves_latest_private_contents(): void
    {
        $owner = $this->buatUser();
        $arsip = $this->buatArsip($owner, 'rollback.pdf', 'arsip_private');
        $pathAwal = $arsip->file_path;
        Storage::disk('public')->put($pathAwal, 'versi public lama');
        Storage::disk('arsip_private')->put($pathAwal, 'versi private terbaru');

        $this->artisan('arsip:migrasikan-ke-private --rollback --dry-run')->assertSuccessful();
        $this->assertSame('arsip_private', $arsip->fresh()->file_disk);

        $this->artisan('arsip:migrasikan-ke-private --rollback --apply')->assertSuccessful();
        $arsip->refresh();
        $this->assertSame('public', $arsip->file_disk);
        $this->assertNotSame($pathAwal, $arsip->file_path);
        Storage::disk('public')->assertExists($pathAwal);
        Storage::disk('public')->assertExists($arsip->file_path);
        Storage::disk('arsip_private')->assertExists($pathAwal);
        $this->assertSame('versi private terbaru', Storage::disk('public')->get($arsip->file_path));
    }

    public function test_model_rejects_unapproved_file_disk(): void
    {
        $owner = $this->buatUser();
        $arsip = $this->buatArsip($owner, 'invalid.pdf', 'public');
        $arsip->file_disk = 's3';

        $this->expectException(LogicException::class);
        $arsip->save();
    }

    public function test_dashboard_counts_referenced_file_once_during_copy_transition(): void
    {
        $user = $this->buatUser();
        $arsip = $this->buatArsip($user, 'duplikat-1.pdf', 'arsip_private');
        $path = 'arsip/salinan-sama.pdf';
        $arsip->file_path = $path;
        $arsip->save();
        $arsipKedua = $this->buatArsip($user, 'duplikat-2.pdf', 'arsip_private');
        $arsipKedua->file_path = $path;
        $arsipKedua->save();
        Storage::disk('arsip_private')->put($path, 'konten');

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('1 file tersimpan');
    }

    private function buatUser(string $username = 'user', string $role = 'user'): User
    {
        return User::create([
            'nama' => ucfirst($username),
            'username' => $username,
            'password' => 'password123',
            'role' => $role,
        ]);
    }

    private function buatArsip(User $owner, string $nama, string $disk): Arsip
    {
        $kategori = Kategori::first() ?? Kategori::create(['nama' => 'Surat Masuk']);

        return Arsip::create([
            'nomor_arsip' => 'ARS-' . strtoupper(substr(md5($nama . $owner->id), 0, 8)),
            'judul' => 'Arsip Uji',
            'kategori_id' => $kategori->id,
            'tanggal_arsip' => '2026-11-03',
            'file_path' => 'arsip/' . $nama,
            'file_disk' => $disk,
            'created_by' => $owner->id,
        ]);
    }
}
