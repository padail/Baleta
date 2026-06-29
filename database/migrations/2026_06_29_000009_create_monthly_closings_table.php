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
            $table->string('closing_number', 80);
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('total_ships')->default(0);
            $table->unsignedInteger('total_invoices')->default(0);
            $table->unsignedInteger('total_boxes')->default(0);
            $table->unsignedBigInteger('total_income')->default(0);
            $table->unsignedBigInteger('total_expense')->default(0);
            $table->bigInteger('net_income')->default(0); // Bersih harian dari invoice, setelah ongkir dan jasa angkat gabus.
            $table->bigInteger('daily_net_income')->default(0);
            $table->unsignedBigInteger('operational_expense_total')->default(0);
            $table->bigInteger('distributable_income')->default(0); // Dasar pembagian kapten-owner.
            $table->decimal('captain_percentage', 5, 2)->default(0);
            $table->bigInteger('captain_share')->default(0);
            $table->bigInteger('owner_share')->default(0);
            $table->unsignedBigInteger('non_operational_expense_total')->default(0);
            $table->bigInteger('owner_final_income')->default(0);
            $table->string('status', 30)->default('approved');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['owner_id', 'closing_number']);
            $table->unique(['owner_id', 'month', 'year'], 'monthly_closing_unique_owner_period');
            $table->index(['owner_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_closings');
    }
};
