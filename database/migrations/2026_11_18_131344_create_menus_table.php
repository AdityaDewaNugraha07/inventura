<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_group_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Contoh: "Dashboard", "Data Barang"
            $table->string('route_name'); // Route tujuan (misal: 'dashboard', 'barang')
            $table->string('icon')->nullable(); // Class ikon atau SVG string
            $table->boolean('is_active')->default(true);
            $table->integer('order_no')->default(0); // Urutan urutan tampil menu
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
