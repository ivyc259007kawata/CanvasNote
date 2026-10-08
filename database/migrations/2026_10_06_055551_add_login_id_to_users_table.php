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
        Schema::table('users', function (Blueprint $table) {
            $table->string('login_id')->nullable()->unique()->after('name');
        });

        DB::table('users')->where('id', 1)->update([
            'login_id' => 'teacher01',
        ]);

        DB::table('users')->where('id', 2)->update([
            'login_id' => 'student01',
        ]);

        DB::table('users')->where('id', 3)->update([
            'login_id' => 'teacher02',
        ]);

        DB::table('users')->where('id', 4)->update([
            'login_id' => 'student02',
        ]);

        DB::table('users')->where('id', 5)->update([
            'login_id' => 'student03',
        ]);

        DB::table('users')->where('id', 6)->update([
            'login_id' => 'student04',
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('login_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['login_id']);
            $table->dropColumn('login_id');
        });
    }
};