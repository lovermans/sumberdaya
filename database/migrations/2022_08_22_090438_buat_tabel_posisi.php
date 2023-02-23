<?php

use Illuminate\Support\Fluent;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Grammars\Grammar;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // The macros should get moved into a service provider but
        // have been put into this migration for the sake of brevity.

        Grammar::macro('typeRaw', function (Fluent $column) {
            return $column->get('raw_type');
        });

        Blueprint::macro('addColumnRaw', function ($rawType, $name) {
            /** @var \Illuminate\Database\Schema\Blueprint $this */
            return $this->addColumn('raw', $name, ['raw_type' => $rawType]);
        });
        
        //DB::unprepared('CREATE TYPE uuid AS UUID;');
        Schema::create('posisis', function (Blueprint $table) {
            $table->id();
            $table->addColumnRaw('uuid', 'posisi_uuid')->default(new Expression('(UUID())'))->unique();
            $table->string('posisi_nama', 40)->unique();
            $table->string('posisi_atasan',40)->nullable();
            $table->string('posisi_wlkp',40)->nullable()->index();
            $table->text('posisi_keterangan')->nullable();
            $table->string('posisi_status',10)->default('AKTIF')->index();
            $table->string('posisi_id_pengunggah',10)->nullable();
            $table->timestamp('posisi_diunggah')->nullable();
            $table->string('posisi_id_pembuat',10)->nullable();
            $table->timestamp('posisi_dibuat')->nullable()->useCurrent();
            $table->string('posisi_id_pengubah',10)->nullable();
            $table->timestamp('posisi_diubah')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posisis');
        //DB::unprepared('DROP TYPE uuid;');
    }
};
