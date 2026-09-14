<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('submission_elements', function (Blueprint $table) {

            $table->id();

            /*
             * どの回答データに属するか
             */
            $table->foreignId('submission_id')
                ->constrained('submissions')
                ->cascadeOnDelete();

            /*
             * ページ番号
             */
            $table->integer('page_number');

            /*
             * Canvas全体を保存
             */
            $table->string('element_type')
                ->default('canvas');

            /*
             * Fabric.jsのCanvas JSON
             */
            $table->json('content')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_elements');
    }
};