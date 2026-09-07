<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('canvas_elements', function (Blueprint $table) {

            $table->id();

            $table->foreignId('lesson_id')
                ->constrained('lessons')
                ->onDelete('cascade');

            // ページ番号
            $table->integer('page_number');

            // 今回はCanvas全体を保存する
            $table->string('element_type')
                ->default('canvas');

            // Fabric.jsのCanvas JSON
            $table->json('content')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canvas_elements');
    }
};