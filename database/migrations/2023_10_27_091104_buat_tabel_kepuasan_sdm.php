<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Fluent;

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
        Schema::create('kepuasansdms', function (Blueprint $table) {
            $table->id();
            $table->addColumnRaw('uuid', 'surveysdm_uuid')->default(new Expression('(UUID())'))->unique();
            $table->string('surveysdm_no_absen', 10);
            $table->year('surveysdm_tahun');
            $table->tinyInteger('surveysdm_1');
            $table->tinyInteger('surveysdm_2');
            $table->tinyInteger('surveysdm_3');
            $table->tinyInteger('surveysdm_4');
            $table->tinyInteger('surveysdm_5');
            $table->tinyInteger('surveysdm_6');
            $table->tinyInteger('surveysdm_7');
            $table->tinyInteger('surveysdm_8');
            $table->tinyInteger('surveysdm_9');
            $table->tinyInteger('surveysdm_10');
            $table->text('surveysdm_saran')->nullable();
            $table->text('surveysdm_keterangan')->nullable();
            $table->string('surveysdm_id_pengunggah', 10)->nullable();
            $table->timestamp('surveysdm_diunggah')->nullable();
            $table->string('surveysdm_id_pembuat', 10)->nullable();
            $table->timestamp('surveysdm_dibuat')->nullable()->useCurrent();
            $table->string('surveysdm_id_pengubah', 10)->nullable();
            $table->timestamp('surveysdm_diubah')->nullable()->useCurrentOnUpdate();
            $table->unique(['surveysdm_no_absen', 'surveysdm_tahun'], 'surveysdm_kunci_komposit_unik');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kepuasansdms');
        //DB::unprepared('DROP TYPE uuid;');
    }
};
