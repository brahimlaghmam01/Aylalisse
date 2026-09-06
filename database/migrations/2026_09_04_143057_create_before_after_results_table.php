<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('before_after_results', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('before_image');
            $table->string('after_image');
            $table->string('hair_type')->nullable();
            $table->string('lissage_type')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_published');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('before_after_results');
    }
};
