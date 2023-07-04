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

        // DB::unprepared('CREATE TYPE uuid AS UUID;');
        Schema::create('penilaiansdms', function (Blueprint $table) {
            $table->id();
            $table->addColumnRaw('uuid', 'nilaisdm_uuid')->default(new Expression('(UUID())'))->unique();
            $table->string('nilaisdm_no_absen', 10);
            $table->year('nilaisdm_tahun');
            $table->string('nilaisdm_periode');
            $table->float('nilaisdm_bobot_hadir')->nullable();
            $table->float('nilaisdm_bobot_sikap')->nullable();
            $table->float('nilaisdm_bobot_target')->nullable();
            $table->text('nilaisdm_tindak_lanjut')->nullable();
            $table->text('nilaisdm_keterangan')->nullable();
            $table->string('nilaisdm_id_pengunggah', 10)->nullable();
            $table->timestamp('nilaisdm_diunggah')->nullable();
            $table->string('nilaisdm_id_pembuat', 10)->nullable();
            $table->timestamp('nilaisdm_dibuat')->nullable()->useCurrent();
            $table->string('nilaisdm_id_pengubah', 10)->nullable();
            $table->timestamp('nilaisdm_diubah')->nullable()->useCurrentOnUpdate();
            $table->unique(['nilaisdm_no_absen', 'nilaisdm_tahun', 'nilaisdm_periode']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penilaiansdms');
        //DB::unprepared('DROP TYPE uuid;');
    }
};
