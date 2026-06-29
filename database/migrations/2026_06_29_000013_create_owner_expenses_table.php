<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('owner_expenses')) {
            return;
        }

        Schema::create('owner_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('monthly_closing_id')->nullable()->constrained('monthly_closings')->nullOnDelete();
            $table->foreignId('ship_id')->nullable()->constrained('ships')->nullOnDelete();
            $table->date('expense_date');
            $table->string('expense_type', 40);
            $table->string('description');
            $table->unsignedBigInteger('amount')->default(0);
            $table->string('status', 30)->default('posted');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['owner_id', 'expense_date']);
            $table->index(['owner_id', 'expense_type', 'status']);
            $table->index(['monthly_closing_id', 'expense_type']);
            $table->index(['ship_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_expenses');
    }
};
