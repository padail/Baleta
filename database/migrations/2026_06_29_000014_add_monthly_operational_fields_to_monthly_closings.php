<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_closings', function (Blueprint $table) {
            if (! Schema::hasColumn('monthly_closings', 'daily_net_income')) {
                $table->bigInteger('daily_net_income')->default(0)->after('net_income');
            }

            if (! Schema::hasColumn('monthly_closings', 'operational_expense_total')) {
                $table->unsignedBigInteger('operational_expense_total')->default(0)->after('daily_net_income');
            }

            if (! Schema::hasColumn('monthly_closings', 'distributable_income')) {
                $table->bigInteger('distributable_income')->default(0)->after('operational_expense_total');
            }

            if (! Schema::hasColumn('monthly_closings', 'non_operational_expense_total')) {
                $table->unsignedBigInteger('non_operational_expense_total')->default(0)->after('owner_share');
            }

            if (! Schema::hasColumn('monthly_closings', 'owner_final_income')) {
                $table->bigInteger('owner_final_income')->default(0)->after('non_operational_expense_total');
            }
        });
    }

    public function down(): void
    {
        // Non-destruktif agar laporan tutup bulan tidak hilang.
    }
};
