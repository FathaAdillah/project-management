<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade')
                ->after('id');

            $table->dropUnique('settings_key_unique');
            $table->unique(['user_id', 'key'], 'settings_user_id_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique('settings_user_id_key_unique');
            $table->unique('key', 'settings_key_unique');

            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
