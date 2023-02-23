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
        Schema::create('penempatans', function (Blueprint $table) {
            $table->id();
            $table->addColumnRaw('uuid', 'penempatan_uuid')->default(new Expression('(UUID())'))->unique();
            $table->string('penempatan_no_absen', 10);
            $table->date('penempatan_mulai')->nullable();
            $table->date('penempatan_selesai')->nullable();
            $table->unsignedTinyInteger('penempatan_ke')->nullable();
            $table->string('penempatan_lokasi',40)->nullable()->index();
            $table->string('penempatan_posisi',40)->nullable()->index();
            $table->string('penempatan_kategori',40)->nullable()->index();
            $table->string('penempatan_kontrak',40)->nullable()->index();
            $table->string('penempatan_pangkat',40)->nullable()->index();
            $table->string('penempatan_golongan',40)->nullable();
            $table->string('penempatan_grup',40)->nullable();
            $table->text('penempatan_keterangan')->nullable();
            $table->string('penempatan_id_pengunggah',10)->nullable();
            $table->timestamp('penempatan_diunggah')->nullable();
            $table->string('penempatan_id_pembuat',10)->nullable();
            $table->timestamp('penempatan_dibuat')->nullable()->useCurrent();
            $table->string('penempatan_id_pengubah',10)->nullable();
            $table->timestamp('penempatan_diubah')->nullable()->useCurrentOnUpdate();
            $table->unique(['penempatan_no_absen', 'penempatan_mulai']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penempatans');
        //DB::unprepared('DROP TYPE uuid;');
    }
};