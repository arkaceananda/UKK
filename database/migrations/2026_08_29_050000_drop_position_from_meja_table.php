<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('meja')) {
            return;
        }

        $columns = Schema::getColumnListing('meja');

        Schema::table('meja', function (Blueprint $table) use ($columns) {
            if (in_array('pos_x', $columns)) {
                $table->dropColumn('pos_x');
            }
            if (in_array('pos_y', $columns)) {
                $table->dropColumn('pos_y');
            }
        });
    }

    public function down(): void
    {
        Schema::table('meja', function (Blueprint $table) {
            $table->unsignedInteger('pos_x')->default(0);
            $table->unsignedInteger('pos_y')->default(0);
        });
    }
};
