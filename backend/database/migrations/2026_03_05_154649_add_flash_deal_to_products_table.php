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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_flash_deal')->default(false)->after('is_featured');
            $table->timestamp('flash_deal_ends_at')->nullable()->after('is_flash_deal');
            $table->unsignedTinyInteger('flash_deal_discount')->nullable()->after('flash_deal_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_flash_deal', 'flash_deal_ends_at', 'flash_deal_discount']);
        });
    }
};
