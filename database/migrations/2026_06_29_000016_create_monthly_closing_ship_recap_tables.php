<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('monthly_closing_ship_items')) {
            Schema::create('monthly_closing_ship_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('monthly_closing_id')->constrained('monthly_closings')->cascadeOnDelete();
                $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('ship_id')->nullable()->constrained('ships')->nullOnDelete();
                $table->foreignId('captain_id')->nullable()->constrained('captains')->nullOnDelete();
                $table->string('ship_name')->nullable();
                $table->string('captain_name')->nullable();
                $table->unsignedInteger('total_invoices')->default(0);
                $table->unsignedInteger('total_boxes')->default(0);
                $table->unsignedBigInteger('total_income')->default(0);
                $table->unsignedBigInteger('total_invoice_expense')->default(0);
                $table->bigInteger('total_daily_net_income')->default(0);
                $table->unsignedBigInteger('total_ship_operational_expense')->default(0);
                $table->bigInteger('net_after_ship_operational')->default(0);
                $table->decimal('captain_percentage', 5, 2)->default(0);
                $table->bigInteger('captain_share')->default(0);
                $table->bigInteger('owner_share')->default(0);
                $table->timestamps();

                $table->unique(['monthly_closing_id', 'ship_id'], 'mcsi_unique_closing_ship');
                $table->index(['owner_id', 'ship_id'], 'mcsi_owner_ship_index');
            });
        }

        if (! Schema::hasTable('monthly_closing_ship_invoice_items')) {
            Schema::create('monthly_closing_ship_invoice_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('monthly_closing_ship_item_id')->constrained('monthly_closing_ship_items')->cascadeOnDelete();
                $table->foreignId('invoice_id')->constrained('fish_delivery_invoices')->restrictOnDelete();
                $table->string('invoice_number', 80)->nullable();
                $table->date('invoice_date');
                $table->unsignedInteger('total_boxes')->default(0);
                $table->unsignedBigInteger('total_income')->default(0);
                $table->unsignedBigInteger('total_expense')->default(0);
                $table->bigInteger('net_income')->default(0);
                $table->timestamps();

                $table->unique('invoice_id', 'mcsi_invoice_unique');
                $table->index(['monthly_closing_ship_item_id', 'invoice_date'], 'mcsii_item_date_index');
            });
        }

        if (! Schema::hasTable('monthly_closing_ship_operational_expenses')) {
            Schema::create('monthly_closing_ship_operational_expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('monthly_closing_ship_item_id')->constrained('monthly_closing_ship_items')->cascadeOnDelete();
                $table->string('description');
                $table->unsignedBigInteger('amount')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_closing_ship_operational_expenses');
        Schema::dropIfExists('monthly_closing_ship_invoice_items');
        Schema::dropIfExists('monthly_closing_ship_items');
    }
};
