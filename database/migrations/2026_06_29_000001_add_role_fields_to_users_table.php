<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable()->after('email');
            }

            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 30)->default('owner')->after('password');
            }

            if (! Schema::hasColumn('users', 'owner_id')) {
                $table->foreignId('owner_id')
                    ->nullable()
                    ->after('role')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('owner_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'owner_id'], 'users_role_owner_id_index');
            $table->index('is_active', 'users_is_active_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_owner_id_index');
            $table->dropIndex('users_is_active_index');
            $table->dropConstrainedForeignId('owner_id');
            $table->dropColumn(['phone', 'role', 'is_active']);
        });
    }
};
