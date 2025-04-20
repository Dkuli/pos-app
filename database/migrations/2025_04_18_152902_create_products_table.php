<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('tax_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('slug')->index();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            $table->text('description')->nullable();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2);
            $table->decimal('wholesale_price', 15, 2)->nullable();
            $table->integer('alert_quantity')->nullable();
            $table->enum('product_type', ['standard', 'digital', 'service'])->default('standard');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_taxable')->default(false);
            $table->boolean('track_inventory')->default(true);
            $table->string('image')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('barcode');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
