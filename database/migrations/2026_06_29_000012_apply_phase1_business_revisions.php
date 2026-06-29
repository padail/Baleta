<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fish_delivery_invoice_items', function (Blueprint $table) {
            if (! Schema::hasColumn('fish_delivery_invoice_items', 'buyer_name')) {
                $table->string('buyer_name')->nullable()->after('buyer_id');
            }
        });

        $this->makeBuyerIdNullable();


        Schema::table('monthly_closings', function (Blueprint $table) {
            if (! Schema::hasColumn('monthly_closings', 'total_ships')) {
                $table->unsignedInteger('total_ships')->default(0)->after('year');
            }
        });

        $this->makeOldClosingColumnsNullable();
        $this->dropOldMonthlyClosingUnique();
        $this->addNewMonthlyClosingUnique();

        Schema::table('monthly_closing_items', function (Blueprint $table) {
            if (! Schema::hasColumn('monthly_closing_items', 'ship_id')) {
                $table->foreignId('ship_id')->nullable()->after('invoice_id')->constrained('ships')->nullOnDelete();
            }

            if (! Schema::hasColumn('monthly_closing_items', 'captain_id')) {
                $table->foreignId('captain_id')->nullable()->after('ship_id')->constrained('captains')->nullOnDelete();
            }

            if (! Schema::hasColumn('monthly_closing_items', 'ship_name')) {
                $table->string('ship_name')->nullable()->after('captain_id');
            }

            if (! Schema::hasColumn('monthly_closing_items', 'captain_name')) {
                $table->string('captain_name')->nullable()->after('ship_name');
            }

            if (! Schema::hasColumn('monthly_closing_items', 'captain_percentage')) {
                $table->decimal('captain_percentage', 5, 2)->default(0)->after('net_income');
            }

            if (! Schema::hasColumn('monthly_closing_items', 'captain_share')) {
                $table->bigInteger('captain_share')->default(0)->after('captain_percentage');
            }

            if (! Schema::hasColumn('monthly_closing_items', 'owner_share')) {
                $table->bigInteger('owner_share')->default(0)->after('captain_share');
            }
        });
    }

    public function down(): void
    {
        // Migration ini bersifat upgrade kompatibilitas. Down dibuat non-destruktif agar data invoice dan tutup bulan tidak hilang.
    }

    private function makeBuyerIdNullable(): void
    {
        if (! Schema::hasColumn('fish_delivery_invoice_items', 'buyer_id')) {
            return;
        }

        $driver = DB::getDriverName();

        try {
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE fish_delivery_invoice_items MODIFY buyer_id BIGINT UNSIGNED NULL');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE fish_delivery_invoice_items ALTER COLUMN buyer_id DROP NOT NULL');
            }
        } catch (Throwable $e) {
            // Abaikan jika database sudah nullable atau driver tidak mendukung alter sederhana.
        }
    }

    private function makeOldClosingColumnsNullable(): void
    {
        $driver = DB::getDriverName();

        foreach (['ship_id', 'captain_id'] as $column) {
            if (! Schema::hasColumn('monthly_closings', $column)) {
                continue;
            }

            try {
                if ($driver === 'mysql') {
                    DB::statement("ALTER TABLE monthly_closings MODIFY {$column} BIGINT UNSIGNED NULL");
                } elseif ($driver === 'pgsql') {
                    DB::statement("ALTER TABLE monthly_closings ALTER COLUMN {$column} DROP NOT NULL");
                }
            } catch (Throwable $e) {
                // Abaikan jika sudah nullable.
            }
        }
    }

    private function dropOldMonthlyClosingUnique(): void
    {
        $driver = DB::getDriverName();

        try {
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE monthly_closings DROP INDEX monthly_closing_unique_period');
            } elseif ($driver === 'pgsql') {
                DB::statement('DROP INDEX IF EXISTS monthly_closing_unique_period');
            }
        } catch (Throwable $e) {
            // Index lama mungkin tidak ada pada fresh install.
        }
    }

    private function addNewMonthlyClosingUnique(): void
    {
        try {
            Schema::table('monthly_closings', function (Blueprint $table) {
                $table->unique(['owner_id', 'month', 'year'], 'monthly_closing_unique_owner_period');
            });
        } catch (Throwable $e) {
            // Index mungkin sudah ada.
        }
    }
};
