<?php

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
        Schema::table('comments', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        DB::table('comments')->orderBy('id')->chunkById(200, function ($comments) {
            foreach ($comments as $comment) {
                DB::table('comments')
                    ->where('id', $comment->id)
                    ->update(['uuid' => (string) Str::uuid()]);
            }
        });

        // doctrine/dbal isn't a dependency, so use a raw statement instead of
        // Blueprint::change() to alter the column to NOT NULL + UNIQUE.
        DB::statement('ALTER TABLE comments MODIFY uuid CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE comments ADD UNIQUE comments_uuid_unique (uuid)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropUnique('comments_uuid_unique');
            $table->dropColumn('uuid');
        });
    }
};
