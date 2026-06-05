<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keyword')->index();
            $table->string('slug')->unique();
            $table->string('language', 10)->default('tr'); // tr, en, ar, ru, fa, fr
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->integer('search_volume')->default(0);
            $table->integer('difficulty')->default(50); // 0-100
            $table->boolean('is_trending')->default(false);
            $table->boolean('is_auto_generated')->default(false);
            $table->integer('clicks')->default(0);
            $table->integer('impressions')->default(0);
            $table->decimal('position', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('taxonomies')->onDelete('set null');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('set null');
            $table->index(['language', 'is_trending']);
            $table->index(['category_id', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keywords');
    }
};