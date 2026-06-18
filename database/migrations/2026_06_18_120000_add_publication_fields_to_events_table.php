<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->enum('publication_visibility', ['public', 'invite_only'])
                ->default('public')
                ->after('status');
            $table->uuid('invite_token')
                ->nullable()
                ->unique()
                ->after('publication_visibility');
            $table->timestamp('published_at')
                ->nullable()
                ->after('invite_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique(['invite_token']);
            $table->dropColumn(['publication_visibility', 'invite_token', 'published_at']);
        });
    }
};