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
        // Check if stores table exists, if not create it
        if (!Schema::hasTable('stores')) {
            Schema::create('stores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('logo')->nullable();
                $table->string('cover')->nullable();
                $table->string('primary_color')->nullable();
                $table->text('description')->nullable();
                $table->string('direccion')->nullable();
                $table->string('telefono')->nullable();
                $table->enum('estado', ['activa', 'inactiva'])->default('activa');
                $table->string('horario_atencion')->nullable();
                $table->string('categoria_principal')->nullable();
                $table->decimal('calificacion_promedio', 3, 2)->default(0);
                $table->timestamps();
            });
        }

        // Add missing columns to orders table
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'store_id')) {
                $table->unsignedBigInteger('store_id')->after('user_id');
                $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            }
            if (!Schema::hasColumn('orders', 'status')) {
                $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending')->after('total');
            }
        });

        // Add missing columns to products table
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'store_id')) {
                $table->foreignId('store_id')->constrained()->onDelete('cascade')->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'store_id')) {
                $table->dropForeign(['store_id']);
                $table->dropColumn('store_id');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'store_id')) {
                $table->dropForeign(['store_id']);
                $table->dropColumn('store_id');
            }
            if (Schema::hasColumn('orders', 'status')) {
                $table->dropColumn('status');
            }
        });

        if (Schema::hasTable('stores')) {
            Schema::dropIfExists('stores');
        }
    }
};
