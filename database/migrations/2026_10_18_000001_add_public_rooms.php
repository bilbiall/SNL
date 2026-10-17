<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Public online rooms: a name and a flag, so they can be listed on /rooms for anyone to join. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $t) {
            $t->string('name', 40)->nullable()->after('join_code');
            $t->boolean('is_public')->default(false)->after('name');
            $t->index(['is_public', 'status', 'updated_at'], 'rooms_public_listing');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $t) {
            $t->dropIndex('rooms_public_listing');
            $t->dropColumn(['name', 'is_public']);
        });
    }
};
