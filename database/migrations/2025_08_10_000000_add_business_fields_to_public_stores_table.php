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
        Schema::table('public_stores', function (Blueprint $table) {
            // Business Information
            $table->string('business_name')->nullable()->after('nombre_tienda');
            $table->string('ruc', 20)->nullable()->after('business_name');
            $table->string('business_type')->nullable()->after('ruc');
            
            // Location Details
            $table->decimal('latitude', 10, 8)->nullable()->after('direccion');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->string('google_maps_url')->nullable()->after('longitude');
            
            // Social Media
            $table->string('facebook_url')->nullable()->after('google_maps_url');
            $table->string('instagram_url')->nullable()->after('facebook_url');
            $table->string('tiktok_url')->nullable()->after('instagram_url');
            $table->string('youtube_url')->nullable()->after('tiktok_url');
            
            // Contact & Web
            $table->string('website_url')->nullable()->after('youtube_url');
            $table->string('email_contacto')->nullable()->after('website_url');
            $table->string('whatsapp_number', 20)->nullable()->after('email_contacto');
            
            // Additional Info
            $table->text('business_description')->nullable()->after('descripcion');
            $table->string('short_description', 500)->nullable()->after('business_description');
            $table->json('tags')->nullable()->after('short_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('public_stores', function (Blueprint $table) {
            $table->dropColumn([
                'business_name',
                'ruc',
                'business_type',
                'latitude',
                'longitude',
                'google_maps_url',
                'facebook_url',
                'instagram_url',
                'tiktok_url',
                'youtube_url',
                'website_url',
                'email_contacto',
                'whatsapp_number',
                'business_description',
                'short_description',
                'tags'
            ]);
        });
    }
};
