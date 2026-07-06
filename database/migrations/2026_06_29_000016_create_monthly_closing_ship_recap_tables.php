<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Migration ini memakai nama foreign key pendek.
         * MySQL membatasi nama constraint maksimal 64 karakter.
         */

        Schema::dropIfExists('monthly_closing_ship_operational_expenses');
        Schema::dropIfExists('monthly_closing_ship_invoice_items');
        Schema::dropIfExists('monthly_closing_ship_items');

        Schema::create('monthly_closing_ship_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('monthly_closing_id');
            $table->foreignId('owner_id');
            $table->foreignId('ship_id');
            $table->foreignId('captain_id')->nullable();

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

            $table->foreign('monthly_closing_id', 'mcsi_closing_fk')
                ->references('id')
                ->on('monthly_closings')
                ->cascadeOnDelete();

            $table->foreign('owner_id', 'mcsi_owner_fk')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('ship_id', 'mcsi_ship_fk')
                ->references('id')
                ->on('ships')
                ->cascadeOnDelete();

            $table->foreign('captain_id', 'mcsi_captain_fk')
                ->references('id')
                ->on('captains')
                ->nullOnDelete();

            $table->index(['monthly_closing_id', 'ship_id'], 'mcsi_closing_ship_idx');
            $table->index(['owner_id', 'ship_id'], 'mcsi_owner_ship_idx');
        });

        Schema::create('monthly_closing_ship_invoice_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('monthly_closing_ship_item_id');
            $table->foreignId('invoice_id');

            $table->date('invoice_date');
            $table->unsignedInteger('total_boxes')->default(0);
            $table->unsignedBigInteger('total_income')->default(0);
            $table->unsignedBigInteger('total_expense')->default(0);
            $table->bigInteger('net_income')->default(0);

            $table->timestamps();

            $table->foreign('monthly_closing_ship_item_id', 'mcsii_ship_item_fk')
                ->references('id')
                ->on('monthly_closing_ship_items')
                ->cascadeOnDelete();

            $table->foreign('invoice_id', 'mcsii_invoice_fk')
                ->references('id')
                ->on('fish_delivery_invoices')
                ->cascadeOnDelete();

            $table->index('monthly_closing_ship_item_id', 'mcsii_ship_item_idx');
            $table->index('invoice_id', 'mcsii_invoice_idx');
        });

        Schema::create('monthly_closing_ship_operational_expenses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('monthly_closing_ship_item_id');
            $table->string('description');
            $table->unsignedBigInteger('amount')->default(0);

            $table->timestamps();

            $table->foreign('monthly_closing_ship_item_id', 'mcsoe_ship_item_fk')
                ->references('id')
                ->on('monthly_closing_ship_items')
                ->cascadeOnDelete();

            $table->index('monthly_closing_ship_item_id', 'mcsoe_ship_item_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_closing_ship_operational_expenses');
        Schema::dropIfExists('monthly_closing_ship_invoice_items');
        Schema::dropIfExists('monthly_closing_ship_items');
    }
};