<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'email_verified_at') || !Schema::hasColumn('users', 'remember_token') || !Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'email_verified_at')) {
                    $table->timestamp('email_verified_at')->nullable();
                }
                if (!Schema::hasColumn('users', 'remember_token')) {
                    $table->rememberToken();
                }
                if (!Schema::hasColumn('users', 'avatar')) {
                    $table->string('avatar')->nullable();
                }
                if (!Schema::hasColumn('users', 'phone')) {
                    $table->string('phone')->nullable();
                }
                if (!Schema::hasColumn('users', 'address')) {
                    $table->string('address')->nullable();
                }
                if (!Schema::hasColumn('users', 'status')) {
                    $table->boolean('status')->default(true);
                }
                if (!Schema::hasColumn('users', 'role_id')) {
                    $table->unsignedBigInteger('role_id')->nullable();
                }
            });

            // Copiar datos antiguos de "avatar" hacia "avatar_path" (si existían)
            try {
                DB::statement('UPDATE users SET avatar = avatar WHERE avatar IS NOT NULL');
            } catch (\Throwable $e) {
                // Si falla por cualquier motivo, no rompemos la migración.
                // (Opcionalmente podrías loguear el error)
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'avatar_path') || Schema::hasColumn('users', 'remember_token') || Schema::hasColumn('users', 'email_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'avatar_path')) {
                    $table->dropColumn('avatar_path');
                }
                if (Schema::hasColumn('users', 'remember_token')) {
                    $table->dropColumn('remember_token');
                }
                if (Schema::hasColumn('users', 'email_verified_at')) {
                    $table->dropColumn('email_verified_at');
                }
            });
        }
    }
};
