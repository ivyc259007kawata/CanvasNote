<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('canvas_elements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lesson_id')
                ->constrained('lessons')
                ->onDelete('cascade');

            $table->string('element_type');
            // text / image / answer_area / drawing

            $table->integer('x');
            $table->integer('y');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();

            $table->json('content')->nullable();
            // テキスト内容・画像URL・設定など

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canvas_elements');
    }
};
