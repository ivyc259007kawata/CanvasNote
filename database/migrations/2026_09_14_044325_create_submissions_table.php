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
        Schema::create('submissions', function (Blueprint $table) {

            $table->id();

            /*
             * どの教材への回答か
             */
            $table->foreignId('lesson_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
             * 回答した生徒
             */
            $table->foreignId('student_id')
                ->constrained('users')
                ->cascadeOnDelete();

            /*
             * 回答の状態
             *
             * draft     = 下書き
             * submitted = 提出済み
             */
            $table->string('status')
                ->default('draft');

            /*
             * 提出日時
             *
             * 下書きの間は null
             */
            $table->timestamp('submitted_at')
                ->nullable();

            $table->timestamps();

            /*
             * 同じ生徒が
             * 同じ教材に対して
             * 複数の回答データを作らないようにする
             */
            $table->unique([
                'lesson_id',
                'student_id'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};