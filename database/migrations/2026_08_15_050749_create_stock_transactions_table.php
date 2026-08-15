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
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('type', [
                'purchase',
                'sale',
                'adjustment',
                'transfer_in',
                'transfer_out',
                'damage',
                'return',
            ]);

            $table->integer('quantity');

            $table->integer('quantity_before');
            $table->integer('quantity_after');

            $table->string('reference')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
