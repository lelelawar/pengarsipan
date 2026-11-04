<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            $table->string('file_disk')->default('public')->after('file_path');
        });
    }

    public function down(): void
    {
        if (DB::table('arsips')->where('file_disk', 'arsip_private')->exists()) {
            throw new RuntimeException('Alihkan seluruh arsip ke public sebelum menghapus kolom file_disk.');
        }

        Schema::table('arsips', function (Blueprint $table) {
            $table->dropColumn('file_disk');
        });
    }
};
