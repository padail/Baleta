<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_closing_items', function (Blueprint $table) {
            if (! Schema::hasColumn('monthly_closing_items', 'operational_expense')) {
                $table->unsignedBigInteger('operational_expense')->default(0)->after('net_income');
            }

            if (! Schema::hasColumn('monthly_closing_items', 'distributable_income')) {
                $table->bigInteger('distributable_income')->default(0)->after('operational_expense');
            }
        });
    }

    public function down(): void
    {
        // Non-destruktif.
    }
};
