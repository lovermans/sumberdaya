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
        Schema::create('tambahsdms', function (Blueprint $table) {
            $table->id();
            $table->addColumnRaw('uuid', 'tambahsdm_uuid')->default(new Expression('(UUID())'))->unique();
            $table->string('tambahsdm_no',20)->unique();
            $table->string('tambahsdm_penempatan',20)->index();
            $table->string('tambahsdm_posisi', 40)->index();
            $table->unsignedTinyInteger('tambahsdm_jumlah')->nullable();
            $table->date('tambahsdm_tgl_diusulkan')->nullable();
            $table->date('tambahsdm_tgl_dibutuhkan')->nullable();
            $table->text('tambahsdm_alasan')->nullable();
            $table->text('tambahsdm_keterangan')->nullable();
            $table->string('tambahsdm_status',10)->default('DIUSULKAN')->index();
            $table->string('tambahsdm_sdm_id',10)->index();
            $table->string('tambahsdm_id_pengunggah',10)->nullable();
            $table->timestamp('tambahsdm_diunggah')->nullable();
            $table->string('tambahsdm_id_pembuat',10)->nullable();
            $table->timestamp('tambahsdm_dibuat')->nullable()->useCurrent();
            $table->string('tambahsdm_id_pengubah',10)->nullable();
            $table->timestamp('tambahsdm_diubah')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tambahsdms');
        //DB::unprepared('DROP TYPE uuid;');
    }
};
