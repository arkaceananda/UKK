<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('transaksi', 'qr_string')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->text('qr_string')->nullable()->after('qr_code_url');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transaksi', 'qr_string')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->dropColumn('qr_string');
            });
        }
    }
};
