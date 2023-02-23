<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::table('penempatans', function (Blueprint $table) {       
            $table->foreign('penempatan_posisi')->references('posisi_nama')->on('posisis')->cascadeOnUpdate()->restrictOnDelete();
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::table('penempatans', function (Blueprint $table) {       
            $table->dropForeign(['penempatan_posisi']);
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
