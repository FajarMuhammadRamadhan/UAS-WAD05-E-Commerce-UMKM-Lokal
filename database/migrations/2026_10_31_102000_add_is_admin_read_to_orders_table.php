<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_admin_read')->default(false)->after('status');
        });

        // Set semua pesanan lama yang sudah ada menjadi sudah dibaca agar notifikasi bersih
        DB::table('orders')->update(['is_admin_read' => true]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('is_admin_read');
        });
    }
};

