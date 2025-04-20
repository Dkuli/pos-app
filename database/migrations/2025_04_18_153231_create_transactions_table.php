<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('transaction_number');
            $table->enum('transaction_type', ['sale', 'return', 'quotation'])->default('sale');
            $table->dateTime('transaction_date');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->decimal('paid', 15, 2);
            $table->decimal('change', 15, 2)->default(0);
            $table->decimal('due', 15, 2)->default(0);
            $table->enum('payment_status', ['paid', 'partial', 'pending'])->default('paid');
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'e-wallet', 'other'])->default('cash');
            $table->string('card_number')->nullable();
            $table->string('card_holder_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('transaction_number');
            $table->index('transaction_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
