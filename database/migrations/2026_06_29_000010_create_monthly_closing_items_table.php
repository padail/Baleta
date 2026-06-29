<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_closing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_closing_id')->constrained('monthly_closings')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('fish_delivery_invoices')->restrictOnDelete();
            $table->foreignId('ship_id')->nullable()->constrained('ships')->nullOnDelete();
            $table->foreignId('captain_id')->nullable()->constrained('captains')->nullOnDelete();
            $table->string('ship_name')->nullable();
            $table->string('captain_name')->nullable();
            $table->date('invoice_date');
            $table->unsignedInteger('total_boxes')->default(0);
            $table->unsignedBigInteger('total_income')->default(0);
            $table->unsignedBigInteger('total_expense')->default(0);
            $table->bigInteger('net_income')->default(0);
            $table->unsignedBigInteger('operational_expense')->default(0);
            $table->bigInteger('distributable_income')->default(0);
            $table->decimal('captain_percentage', 5, 2)->default(0);
            $table->bigInteger('captain_share')->default(0);
            $table->bigInteger('owner_share')->default(0);
            $table->timestamps();

            $table->unique('invoice_id');
            $table->index(['monthly_closing_id', 'invoice_date'], 'mci_closing_date_index');
            $table->index(['monthly_closing_id', 'ship_id'], 'mci_closing_ship_index');
            $table->index(['monthly_closing_id', 'captain_id'], 'mci_closing_captain_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_closing_items');
    }
};
