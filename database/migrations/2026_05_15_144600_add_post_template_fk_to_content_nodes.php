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
        Schema::table('content_nodes', function (Blueprint $table) {
            $table->foreign('post_template_id')
                ->references('id')->on('post_templates')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_nodes', function (Blueprint $table) {
            $table->dropForeign(['post_template_id']);
        });
    }
};
?>