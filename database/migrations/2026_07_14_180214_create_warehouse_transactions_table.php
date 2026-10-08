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
        Schema::create('warehouse_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('driver_id');
            $table->string('type');
            $table->json('items')->nullable();
            $table->decimal('declared_cash', 12, 2);
            $table->decimal('comptage_cash', 12, 2);
            $table->decimal('discrepancy', 12, 2)->default(0);
            $table->string('status')->default('validated');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_transactions');
    }
};