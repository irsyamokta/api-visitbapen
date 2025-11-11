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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->text('title');
            $table->enum('type', ['income', 'expense']);
            $table->string('category');
            $table->integer('amount');
            $table->enum('finance_role', ['finance_batik', 'finance_tourism', 'admin_batik', 'admin_tourism']);
            $table->string('financier')->nullable();
            $table->dateTime('transaction_date');
            $table->uuid('order_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
