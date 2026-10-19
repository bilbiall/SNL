<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Card packs, usually sponsored: a named set of cards with an optional sponsor, a date range and the modes it appears in. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packs', function (Blueprint $t) {
            $t->id();
            $t->string('name', 60);
            $t->string('sponsor', 60)->nullable();      // "Presented by …" on every card of the pack; empty = a plain themed pack
            $t->string('tagline', 120)->nullable();
            $t->string('logo', 200)->nullable();        // /uploads/packs/… (shown on the TV sponsor strip)
            $t->string('link', 200)->nullable();
            $t->json('modes')->nullable();              // null = every mode
            $t->date('starts_on')->nullable();
            $t->date('ends_on')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::table('cards', function (Blueprint $t) {
            $t->unsignedBigInteger('pack_id')->nullable()->after('board_id');
            $t->index('pack_id');
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $t) {
            $t->dropIndex(['pack_id']);
            $t->dropColumn('pack_id');
        });
        Schema::dropIfExists('packs');
    }
};
