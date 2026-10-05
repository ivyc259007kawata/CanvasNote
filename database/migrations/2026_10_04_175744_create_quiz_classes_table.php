<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quiz_classes', function (Blueprint $table) {
            $table->id();

            // クイズ
            $table->foreignId('quiz_id')
                ->constrained('quizzes')
                ->cascadeOnDelete();

            // 出題対象のクラス
            $table->foreignId('class_id')
                ->constrained('classes')
                ->cascadeOnDelete();

            $table->timestamps();

            // 同じクイズを同じクラスに重複登録しない
            $table->unique(['quiz_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_classes');
    }
};