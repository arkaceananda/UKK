<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recaps', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // daily, weekly, monthly
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->integer('total_orders')->default(0);
            $table->integer('total_items_sold')->default(0);
            $table->json('top_menus')->nullable(); // [{nama, jumlah}]
            $table->json('daily_breakdown')->nullable(); // [{date, revenue, orders}]
            $table->boolean('is_finalized')->default(false);
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->unique(['type', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recaps');
    }
};
