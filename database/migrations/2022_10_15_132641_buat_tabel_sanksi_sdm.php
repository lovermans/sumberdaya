<?php

use Illuminate\Support\Fluent;
use Illuminate\Support\Facades\DB;
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
        Schema::create('sanksisdms', function (Blueprint $table) {
            $table->id();
            $table->addColumnRaw('uuid', 'sanksi_uuid')->default(new Expression('(UUID())'))->unique();
            $table->string('sanksi_no_absen', 10);
            $table->string('sanksi_jenis',40);
            $table->date('sanksi_mulai');
            $table->date('sanksi_selesai');
            $table->string('sanksi_lap_no',20)->nullable();
            $table->text('sanksi_tambahan')->nullable();
            $table->text('sanksi_keterangan')->nullable();
            $table->string('sanksi_id_pengunggah',10)->nullable();
            $table->timestamp('sanksi_diunggah')->nullable();
            $table->string('sanksi_id_pembuat',10)->nullable();
            $table->timestamp('sanksi_dibuat')->nullable()->useCurrent();
            $table->string('sanksi_id_pengubah',10)->nullable();
            $table->timestamp('sanksi_diubah')->nullable()->useCurrentOnUpdate();
            $table->unique(['sanksi_no_absen', 'sanksi_jenis', 'sanksi_mulai']);
        });
        
        // Schema::table('sanksisdms', function (Blueprint $table) {
        //     DB::statement('alter table `sanksisdms` ADD PERIOD FOR p(sanksi_mulai, sanksi_selesai)');
        //     DB::statement('alter table `sanksisdms` add unique `periode_sanksi`(sanksi_no_absen, sanksi_jenis, p WITHOUT OVERLAPS)');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sanksisdms');
        //DB::unprepared('DROP TYPE uuid;');
    }
};
