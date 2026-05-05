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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();                     // публичный идентификатор для безопасных ссылок
            $table->string('model_type');                       // 'catalog_product', 'auth_client'
            $table->unsignedBigInteger('model_id');
            $table->string('type')->default('image');           // image, icon, gallery
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->string('disk');
            $table->unsignedBigInteger('size');
            $table->json('custom_properties')->nullable();      // дополнительные данные
            $table->timestamps();

            $table->index(['model_type', 'model_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
