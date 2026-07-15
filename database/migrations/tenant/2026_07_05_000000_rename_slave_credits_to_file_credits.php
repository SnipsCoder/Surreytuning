<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Renames the "slave credits" concept to "file credits" in existing tenant
 * databases. Fully guarded/idempotent so it is safe to run against tenants
 * that were provisioned before or after the rename, and safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. dealers.slave_credit_balance -> file_credit_balance
        if (Schema::hasColumn('dealers', 'slave_credit_balance')
            && ! Schema::hasColumn('dealers', 'file_credit_balance')) {
            Schema::table('dealers', function (Blueprint $table) {
                $table->renameColumn('slave_credit_balance', 'file_credit_balance');
            });
        }

        // 2. slave_credit_transactions -> file_credit_transactions
        if (Schema::hasTable('slave_credit_transactions')
            && ! Schema::hasTable('file_credit_transactions')) {
            Schema::rename('slave_credit_transactions', 'file_credit_transactions');
        }

        // 3. products.payment_type enum value slave_credits -> file_credits
        // Raw ENUM redefinition is MySQL-only; other drivers (e.g. SQLite used
        // in tests) store the column as text with the new values already.
        if ($this->isMysql() && Schema::hasColumn('products', 'payment_type')) {
            DB::statement(
                'ALTER TABLE `products` MODIFY COLUMN `payment_type` '
                ."ENUM('slave_credits', 'file_credits', 'direct_payment', 'both') NOT NULL DEFAULT 'both'"
            );
            DB::table('products')->where('payment_type', 'slave_credits')
                ->update(['payment_type' => 'file_credits']);
            DB::statement(
                'ALTER TABLE `products` MODIFY COLUMN `payment_type` '
                ."ENUM('file_credits', 'direct_payment', 'both') NOT NULL DEFAULT 'both'"
            );
        }

        // 4. product_orders.payment_method enum value slave_credits -> file_credits
        if ($this->isMysql() && Schema::hasColumn('product_orders', 'payment_method')) {
            DB::statement(
                'ALTER TABLE `product_orders` MODIFY COLUMN `payment_method` '
                ."ENUM('slave_credits', 'file_credits', 'stripe') NOT NULL"
            );
            DB::table('product_orders')->where('payment_method', 'slave_credits')
                ->update(['payment_method' => 'file_credits']);
            DB::statement(
                'ALTER TABLE `product_orders` MODIFY COLUMN `payment_method` '
                ."ENUM('file_credits', 'stripe') NOT NULL"
            );
        }
    }

    public function down(): void
    {
        if ($this->isMysql() && Schema::hasColumn('product_orders', 'payment_method')) {
            DB::statement(
                'ALTER TABLE `product_orders` MODIFY COLUMN `payment_method` '
                ."ENUM('slave_credits', 'file_credits', 'stripe') NOT NULL"
            );
            DB::table('product_orders')->where('payment_method', 'file_credits')
                ->update(['payment_method' => 'slave_credits']);
            DB::statement(
                'ALTER TABLE `product_orders` MODIFY COLUMN `payment_method` '
                ."ENUM('slave_credits', 'stripe') NOT NULL"
            );
        }

        if ($this->isMysql() && Schema::hasColumn('products', 'payment_type')) {
            DB::statement(
                'ALTER TABLE `products` MODIFY COLUMN `payment_type` '
                ."ENUM('slave_credits', 'file_credits', 'direct_payment', 'both') NOT NULL DEFAULT 'both'"
            );
            DB::table('products')->where('payment_type', 'file_credits')
                ->update(['payment_type' => 'slave_credits']);
            DB::statement(
                'ALTER TABLE `products` MODIFY COLUMN `payment_type` '
                ."ENUM('slave_credits', 'direct_payment', 'both') NOT NULL DEFAULT 'both'"
            );
        }

        if (Schema::hasTable('file_credit_transactions')
            && ! Schema::hasTable('slave_credit_transactions')) {
            Schema::rename('file_credit_transactions', 'slave_credit_transactions');
        }

        if (Schema::hasColumn('dealers', 'file_credit_balance')
            && ! Schema::hasColumn('dealers', 'slave_credit_balance')) {
            Schema::table('dealers', function (Blueprint $table) {
                $table->renameColumn('file_credit_balance', 'slave_credit_balance');
            });
        }
    }

    private function isMysql(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
