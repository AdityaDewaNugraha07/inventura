<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('menu_group_id')->constrained('menus')->cascadeOnDelete();
            $table->string('route_name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
            $table->string('route_name')->nullable(false)->change();
        });
    }
};