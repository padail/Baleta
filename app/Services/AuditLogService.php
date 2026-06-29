<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    public function record(string $action, Model|string $entity, ?array $oldValues = null, ?array $newValues = null): void
    {
        $request = request();
        $user = auth()->user();

        if (! $user) {
            return;
        }

        AuditLog::create([
            'owner_id' => method_exists($user, 'activeOwnerId') ? $user->activeOwnerId() : null,
            'user_id' => $user->id,
            'action' => $action,
            'entity_type' => is_string($entity) ? $entity : $entity::class,
            'entity_id' => is_string($entity) ? null : $entity->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request instanceof Request ? $request->ip() : null,
            'user_agent' => $request instanceof Request ? substr((string) $request->userAgent(), 0, 500) : null,
            'created_at' => now(),
        ]);
    }
}
