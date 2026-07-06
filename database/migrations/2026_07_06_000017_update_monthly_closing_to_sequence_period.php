<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_closings', function (Blueprint $table) {
            if (! Schema::hasColumn('monthly_closings', 'closing_period_number')) {
                $table->unsignedInteger('closing_period_number')->nullable()->after('closing_number');
            }

            if (! Schema::hasColumn('monthly_closings', 'period_label')) {
                $table->string('period_label', 100)->nullable()->after('closing_period_number');
            }

            if (! Schema::hasColumn('monthly_closings', 'period_started_at')) {
                $table->date('period_started_at')->nullable()->after('year');
            }

            if (! Schema::hasColumn('monthly_closings', 'period_ended_at')) {
                $table->date('period_ended_at')->nullable()->after('period_started_at');
            }

            if (! Schema::hasColumn('monthly_closings', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('approved_at');
            }
        });

        $this->fillExistingPeriodNumbers();
        $this->dropOldCalendarUnique();
        $this->addNewPeriodUnique();
    }

    public function down(): void
    {
        // Non-destruktif. Kolom periode urut dipertahankan agar rekap yang sudah dibuat tidak rusak.
    }

    private function fillExistingPeriodNumbers(): void
    {
        $ownerIds = DB::table('monthly_closings')
            ->select('owner_id')
            ->distinct()
            ->pluck('owner_id');

        foreach ($ownerIds as $ownerId) {
            $closings = DB::table('monthly_closings')
                ->where('owner_id', $ownerId)
                ->orderBy('year')
                ->orderBy('month')
                ->orderBy('id')
                ->get(['id', 'closing_period_number']);

            $number = 1;
            foreach ($closings as $closing) {
                if ($closing->closing_period_number) {
                    $number = max($number, ((int) $closing->closing_period_number) + 1);
                    continue;
                }

                DB::table('monthly_closings')
                    ->where('id', $closing->id)
                    ->update([
                        'closing_period_number' => $number,
                        'period_label' => 'Tutup Bulan '.$number,
                        'closed_at' => DB::raw('COALESCE(approved_at, created_at)'),
                    ]);

                $number++;
            }
        }
    }

    private function dropOldCalendarUnique(): void
    {
        $driver = DB::getDriverName();

        try {
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE monthly_closings DROP INDEX monthly_closing_unique_owner_period');
            } elseif ($driver === 'pgsql') {
                DB::statement('DROP INDEX IF EXISTS monthly_closing_unique_owner_period');
            }
        } catch (Throwable $e) {
            // Index mungkin sudah tidak ada.
        }
    }

    private function addNewPeriodUnique(): void
    {
        try {
            Schema::table('monthly_closings', function (Blueprint $table) {
                $table->unique(['owner_id', 'closing_period_number'], 'monthly_closing_unique_owner_sequence');
                $table->index(['owner_id', 'closing_period_number'], 'monthly_closing_owner_sequence_index');
                $table->index(['owner_id', 'closed_at'], 'monthly_closing_owner_closed_at_index');
            });
        } catch (Throwable $e) {
            // Index mungkin sudah ada.
        }
    }
};
