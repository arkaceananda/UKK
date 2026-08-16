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
        Schema::table('menu', function (Blueprint $table) {
            $table->json('options')->nullable()->after('harga');
        });

        Schema::table('detail_pesanan', function (Blueprint $table) {
            $table->string('selected_option')->nullable()->after('menu_id');
        });

        Schema::table('pesanan', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('status'); // Adjust 'after' based on your schema
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu', function (Blueprint $table) {
            $table->dropColumn('options');
        });

        Schema::table('detail_pesanan', function (Blueprint $table) {
            $table->dropColumn('selected_option');
        });

        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
