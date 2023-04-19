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
        Schema::create('loan_terms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('loan_id');
            $table->integer('term_number');
            $table->decimal('amount', 8, 2);
            $table->timestamp('due_date');
            $table->decimal('paid_amount', 8, 2)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('state');
            $table->timestamps();

            $table->foreign('loan_id')->references('id')->on('loans');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_terms');
    }
};
