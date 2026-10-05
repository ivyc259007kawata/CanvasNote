<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            // クイズを作成した先生
            $table->foreignId('created_by')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->cascadeOnDelete();

            // single = 1問クイズ / test = 小テスト
            $table->string('mode')
                ->default('single')
                ->after('title');

            // draft = 作成済み / active = 出題中 / ended = 終了
            $table->string('status')
                ->default('draft')
                ->after('mode');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'created_by',
                'mode',
                'status',
            ]);
        });
    }
};