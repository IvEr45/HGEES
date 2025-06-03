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
        Schema::create('polygons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('coordinates'); // Store polygon coordinates as JSON
            $table->string('stroke_color')->default('#FF0000');
            $table->decimal('stroke_opacity', 3, 2)->default(0.8);
            $table->integer('stroke_weight')->default(2);
            $table->string('fill_color')->default('#FF0000');
            $table->decimal('fill_opacity', 3, 2)->default(0.35);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polygons');
    }
};