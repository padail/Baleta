<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fish_delivery_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ship_id')->constrained('ships')->restrictOnDelete();
            $table->foreignId('captain_id')->constrained('captains')->restrictOnDelete();
            $table->string('invoice_number', 80);
            $table->date('invoice_date');
            $table->string('carrier_boat_name')->nullable();
            $table->unsignedInteger('total_boxes')->default(0);
            $table->unsignedBigInteger('shipping_cost')->default(0);
            $table->unsignedBigInteger('unloading_cost_per_box')->default(0);
            $table->unsignedBigInteger('total_unloading_cost')->default(0);
            $table->unsignedBigInteger('additional_expense')->default(0);
            $table->unsignedBigInteger('total_expense')->default(0);
            $table->unsignedBigInteger('total_income')->default(0);
            $table->bigInteger('net_income')->default(0);
            $table->string('status', 30)->default('draft');
            $table->text('notes')->nullable();
            $table->uuid('client_uuid')->nullable();
            $table->string('sync_status', 30)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['owner_id', 'invoice_number']);
            $table->unique('client_uuid');
            $table->index(['owner_id', 'invoice_date']);
            $table->index(['owner_id', 'ship_id', 'invoice_date'], 'fdi_owner_ship_date_index');
            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fish_delivery_invoices');
    }
};
