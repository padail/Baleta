<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fish_delivery_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('fish_delivery_invoices')->cascadeOnDelete();
            $table->foreignId('buyer_id')->nullable()->constrained('buyers')->nullOnDelete();
            $table->string('buyer_name');
            $table->string('fish_type')->nullable();
            $table->unsignedInteger('box_count')->default(0);
            $table->unsignedBigInteger('price_per_box')->default(0);
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'buyer_name']);
            $table->index('buyer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fish_delivery_invoice_items');
    }
};
