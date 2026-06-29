<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ship_captain_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ship_id')->constrained('ships')->cascadeOnDelete();
            $table->foreignId('captain_id')->constrained('captains')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['owner_id', 'ship_id', 'is_active'], 'sca_owner_ship_active_index');
            $table->index(['owner_id', 'captain_id', 'is_active'], 'sca_owner_captain_active_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ship_captain_assignments');
    }
};
