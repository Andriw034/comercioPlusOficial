<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_requests')) {
            Schema::table('purchase_requests', function (Blueprint $table) {
                if (! Schema::hasColumn('purchase_requests', 'generation_type')) {
                    $table->enum('generation_type', ['manual', 'automatic'])->default('manual')->after('status');
                }

                if (! Schema::hasColumn('purchase_requests', 'generated_at')) {
                    $table->timestamp('generated_at')->nullable()->after('received_at');
                }
            });
        }

        if (Schema::hasTable('purchase_request_items')) {
            Schema::table('purchase_request_items', function (Blueprint $table) {
                if (! Schema::hasColumn('purchase_request_items', 'ai_reasoning')) {
                    $table->text('ai_reasoning')->nullable()->after('last_cost');
                }

                if (! Schema::hasColumn('purchase_request_items', 'prediction_data')) {
                    $table->json('prediction_data')->nullable()->after('ai_reasoning');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchase_request_items')) {
            Schema::table('purchase_request_items', function (Blueprint $table) {
                $drop = [];

                if (Schema::hasColumn('purchase_request_items', 'prediction_data')) {
                    $drop[] = 'prediction_data';
                }

                if (Schema::hasColumn('purchase_request_items', 'ai_reasoning')) {
                    $drop[] = 'ai_reasoning';
                }

                if ($drop !== []) {
                    $table->dropColumn($drop);
                }
            });
        }

        if (Schema::hasTable('purchase_requests')) {
            Schema::table('purchase_requests', function (Blueprint $table) {
                $drop = [];

                if (Schema::hasColumn('purchase_requests', 'generated_at')) {
                    $drop[] = 'generated_at';
                }

                if (Schema::hasColumn('purchase_requests', 'generation_type')) {
                    $drop[] = 'generation_type';
                }

                if ($drop !== []) {
                    $table->dropColumn($drop);
                }
            });
        }
    }
};
