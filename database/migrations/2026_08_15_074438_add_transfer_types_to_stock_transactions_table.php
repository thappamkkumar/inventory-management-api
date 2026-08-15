<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE stock_transactions
            MODIFY COLUMN type ENUM(
                'purchase',
                'sale',
                'adjustment',
                'damage',
                'return',
                'transfer_in',
                'transfer_out'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE stock_transactions
            MODIFY COLUMN type ENUM(
                'purchase',
                'sale',
                'adjustment',
                'damage',
                'return'
            ) NOT NULL
        ");
    }
};