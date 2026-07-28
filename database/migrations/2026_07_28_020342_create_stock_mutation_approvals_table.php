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
        Schema::create('stock_mutation_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('stock_mutation_id');
            $table->uuid('approved_by');
            $table->enum('decision', ['approved', 'rejected']);
            $table->json('approval_notes')->nullable();
            $table->datetime('approved_at');
            $table->timestamps();

            $table->foreign('stock_mutation_id')->references('id')->on('stock_mutations')->cascadeOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_mutation_approvals');
    }
};
