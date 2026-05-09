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
        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('container_type', 50); // page, post
            $table->unsignedBigInteger('container_id');
            $table->foreignId('widget_instance_id')->constrained('widget_instances')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('section', 50)->nullable();
            $table->string('caption')->nullable();
            $table->timestamps();
            $table->index(['container_type', 'container_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_blocks');
    }
};
