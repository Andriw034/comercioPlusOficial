<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // BÃ¡sicos
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable(); // verificaciÃ³n de correo
            $table->string('password');

            // Perfil
            $table->string('phone', 30)->nullable();
            $table->string('avatar_path')->nullable(); // ruta de imagen (storage/app/public/...)
            $table->boolean('status')->default(true);
            $table->string('address')->nullable();

            // Rol (opcional) - si borran el rol, queda null
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();

            // Tokens de sesiÃ³n â€œrecuÃ©rdameâ€
            $table->rememberToken();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
