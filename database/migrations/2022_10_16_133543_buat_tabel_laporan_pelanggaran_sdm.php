<?php

use Illuminate\Support\Fluent;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Grammars\Grammar;

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
        Schema::create('pelanggaransdms', function (Blueprint $table) {
            $table->id();
            $table->addColumnRaw('uuid', 'langgar_uuid')->default(new Expression('(UUID())'))->unique();
            $table->string('langgar_lap_no',20);
            $table->string('langgar_no_absen', 10);
            $table->string('langgar_pelapor', 10)->index();
            $table->date('langgar_tanggal');
            $table->string('langgar_status', 40)->nullable();
            $table->text('langgar_isi')->nullable();
            $table->text('langgar_keterangan')->nullable();
            $table->string('langgar_id_pengunggah',10)->nullable();
            $table->timestamp('langgar_diunggah')->nullable();
            $table->string('langgar_id_pembuat',10)->nullable();
            $table->timestamp('langgar_dibuat')->nullable()->useCurrent();
            $table->string('langgar_id_pengubah',10)->nullable();
            $table->timestamp('langgar_diubah')->nullable()->useCurrentOnUpdate();
            $table->unique(['langgar_lap_no', 'langgar_no_absen']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pelanggaransdms');
        //DB::unprepared('DROP TYPE uuid;');
    }
};
