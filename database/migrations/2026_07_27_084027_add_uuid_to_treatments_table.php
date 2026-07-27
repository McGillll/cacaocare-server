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
        Schema::table('treatments', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        DB::table('treatments')->select('id')->orderBy('id')->each(function ($treatment) {
            DB::table('treatments')
                ->where('id', $treatment->id)
                ->update(['uuid' => (string) Str::uuid()]);
        });

        // doctrine/dbal is not a dependency, so ->change() is unavailable; alter via raw SQL instead.
        DB::statement('ALTER TABLE treatments MODIFY uuid CHAR(36) NOT NULL');

        Schema::table('treatments', function (Blueprint $table) {
            $table->unique('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
