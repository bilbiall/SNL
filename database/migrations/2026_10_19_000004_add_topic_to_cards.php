<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Cards can belong to a topic (football, movies, history …) so a table can choose what its questions are about. null = a general card. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $t) {
            $t->string('topic', 20)->nullable()->after('pack_id');
            $t->index(['kind', 'topic', 'mode', 'is_active'], 'cards_topic_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $t) {
            $t->dropIndex('cards_topic_lookup');
            $t->dropColumn('topic');
        });
    }
};
