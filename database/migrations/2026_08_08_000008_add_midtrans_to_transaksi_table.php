<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->string('midtrans_transaction_id')->nullable()->after('status_bayar');
            $table->string('midtrans_order_id')->nullable()->after('midtrans_transaction_id');
            $table->text('qr_code_url')->nullable()->after('midtrans_order_id');
            $table->text('qr_string')->nullable()->after('qr_code_url');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn(['midtrans_transaction_id', 'midtrans_order_id', 'qr_code_url', 'qr_string']);
        });
    }
};
