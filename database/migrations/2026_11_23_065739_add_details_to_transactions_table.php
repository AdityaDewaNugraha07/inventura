<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'reference_code')) {
                $table->string('reference_code')->unique()->nullable()->after('id');
            }
            if (!Schema::hasColumn('transactions', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('product_id')->constrained('suppliers')->nullOnDelete();
            }
            if (!Schema::hasColumn('transactions', 'transaction_date')) {
                $table->date('transaction_date')->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('transactions', 'notes')) {
                $table->text('notes')->nullable()->after('transaction_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'supplier_id')) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            }
            foreach (['reference_code', 'transaction_date', 'notes'] as $col) {
                if (Schema::hasColumn('transactions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};