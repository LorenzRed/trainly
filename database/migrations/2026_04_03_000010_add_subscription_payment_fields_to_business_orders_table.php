<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('business_orders', function (Blueprint $table): void {
            $table->unsignedBigInteger('subscription_id')->nullable()->after('product_id');
            $table->string('gcash_name', 120)->nullable()->after('payment_status');
            $table->string('gcash_number', 20)->nullable()->after('gcash_name');
            $table->string('gcash_reference', 80)->nullable()->after('gcash_number');
        });

        DB::statement('ALTER TABLE business_orders MODIFY product_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE business_orders ADD CONSTRAINT business_orders_subscription_id_foreign FOREIGN KEY (subscription_id) REFERENCES business_subscriptions(id) ON DELETE SET NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE business_orders DROP FOREIGN KEY business_orders_subscription_id_foreign');
        DB::statement('ALTER TABLE business_orders MODIFY product_id BIGINT UNSIGNED NOT NULL');

        Schema::table('business_orders', function (Blueprint $table): void {
            $table->dropColumn(['subscription_id', 'gcash_name', 'gcash_number', 'gcash_reference']);
        });
    }
};
