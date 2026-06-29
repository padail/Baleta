<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('fish_delivery_invoices')->cascadeOnDelete();
            $table->string('expense_type', 30);
            $table->string('description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->unsignedBigInteger('amount')->default(0);
            $table->timestamps();

            $table->index(['invoice_id', 'expense_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_expenses');
    }
};
