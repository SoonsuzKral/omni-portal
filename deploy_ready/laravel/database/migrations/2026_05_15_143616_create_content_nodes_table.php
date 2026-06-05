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
        Schema::create('content_nodes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('post_template_id')->nullable();
            $table->unsignedBigInteger('taxonomy_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('seo_title');
            $table->string('slug')->unique();
            $table->text('body_content');
            $table->boolean('is_restricted_content')->default(false);
            $table->bigInteger('page_views')->default(0);
            $table->timestamp('publish_date')->nullable();
            $table->timestamps();
            $table->foreign('taxonomy_id')->references('id')->on('taxonomies')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('set null');
            // Foreign key for post_template_id will be added in a separate migration to avoid ordering issues
            $table->index('taxonomy_id');
            $table->index('location_id');
            $table->index('post_template_id');
                        $table->index('slug');
            $table->index('is_restricted_content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_nodes');
    }
};
