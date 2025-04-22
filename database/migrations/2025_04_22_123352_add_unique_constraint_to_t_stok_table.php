<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueConstraintToTStokTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('t_stok', function (Blueprint $table) {
            // Add a unique constraint on the combination of user_id, barang_id, and supplier_id
            $table->unique(['user_id', 'barang_id', 'supplier_id'], 'unique_stok_constraint');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('t_stok', function (Blueprint $table) {
            // Remove the unique constraint
            $table->dropUnique('unique_stok_constraint');
        });
    }
}