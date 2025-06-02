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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->double('price', 10, 2);
            $table->double('compare_price', 10, 2)->nullable();
            $table->integer('quantity');
            $table->string('image')->nullable();
            $table->Integer('status')->default(1);
            $table->enum('is_featured', ['yes', 'no'])->default('no');
            $table->string('sku')->nullable();

            // product parameter
            $table->string('resolution')->nullable();
            $table->string('infrared')->nullable();
            $table->string('sound')->nullable();
            $table->string('smart_function')->nullable();
            $table->string('AI_function')->nullable();
            $table->string('network')->nullable();
            $table->string('other_features')->nullable();

            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('brand_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
