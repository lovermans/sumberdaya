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
        Schema::create('aturs', function (Blueprint $table) {
            $table->id();
            $table->addColumnRaw('uuid', 'atur_uuid')->default(new Expression('(UUID())'))->unique();
            $table->string('atur_jenis',20);
            $table->string('atur_butir',40);
            $table->text('atur_detail')->nullable();
            $table->string('atur_status',10)->default('AKTIF')->index();
            $table->string('atur_id_pengunggah',10)->nullable();
            $table->timestamp('atur_diunggah')->nullable();
            $table->string('atur_id_pembuat',10)->nullable();
            $table->timestamp('atur_dibuat')->nullable()->useCurrent();
            $table->string('atur_id_pengubah',10)->nullable();
            $table->timestamp('atur_diubah')->nullable()->useCurrentOnUpdate();
            $table->unique(['atur_jenis', 'atur_butir']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aturs');
        //DB::unprepared('DROP TYPE uuid;');
    }
};
