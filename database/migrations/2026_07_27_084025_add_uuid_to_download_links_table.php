<?php

use App\Models\DownloadLinks;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('download_links', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        DownloadLinks::query()->chunkById(100, function ($downloadLinks) {
            foreach ($downloadLinks as $downloadLink) {
                $downloadLink->uuid = (string) Str::uuid();
                $downloadLink->save();
            }
        });

        DB::statement('ALTER TABLE download_links MODIFY uuid CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE download_links ADD UNIQUE download_links_uuid_unique (uuid)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('download_links', function (Blueprint $table) {
            $table->dropUnique('download_links_uuid_unique');
            $table->dropColumn('uuid');
        });
    }
};
