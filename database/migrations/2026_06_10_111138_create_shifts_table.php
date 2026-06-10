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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')
                ->constrained('zones')
                ->cascadeOnDelete();
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->integer('capacity')->default(1);
            $table->foreignId('required_skill_id')
                ->nullable()
                ->constrained('skills')
                ->nullOnDelete();
            $table->enum('status', ['open', 'full', 'closed', 'cancelled'])->default('open');
            $table->timestamps();

            $table->index(['status', 'starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
