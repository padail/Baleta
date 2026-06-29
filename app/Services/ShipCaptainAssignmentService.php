<?php

namespace App\Services;

use App\Models\Captain;
use App\Models\Ship;
use App\Models\ShipCaptainAssignment;
use Illuminate\Support\Facades\DB;

class ShipCaptainAssignmentService
{
    public function assign(Ship $ship, Captain $captain, ?string $startDate = null): ShipCaptainAssignment
    {
        return DB::transaction(function () use ($ship, $captain, $startDate) {
            ShipCaptainAssignment::query()
                ->where('owner_id', $ship->owner_id)
                ->where('ship_id', $ship->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'end_date' => now()->toDateString(),
                ]);

            return ShipCaptainAssignment::create([
                'owner_id' => $ship->owner_id,
                'ship_id' => $ship->id,
                'captain_id' => $captain->id,
                'start_date' => $startDate ?: now()->toDateString(),
                'end_date' => null,
                'is_active' => true,
            ]);
        });
    }
}
