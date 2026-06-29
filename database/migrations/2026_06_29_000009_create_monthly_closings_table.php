<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ship_id')->constrained('ships')->restrictOnDelete();
            $table->foreignId('captain_id')->constrained('captains')->restrictOnDelete();
            $table->string('closing_number', 80);
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('total_invoices')->default(0);
            $table->unsignedInteger('total_boxes')->default(0);
            $table->unsignedBigInteger('total_income')->default(0);
            $table->unsignedBigInteger('total_expense')->default(0);
            $table->bigInteger('net_income')->default(0);
            $table->decimal('captain_percentage', 5, 2)->default(0);
            $table->bigInteger('captain_share')->default(0);
            $table->bigInteger('owner_share')->default(0);
            $table->string('status', 30)->default('approved');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['owner_id', 'closing_number']);
            $table->unique(['owner_id', 'ship_id', 'month', 'year'], 'monthly_closing_unique_period');
            $table->index(['owner_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_closings');
    }
};
