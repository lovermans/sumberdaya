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
        Schema::create('sdms', function (Blueprint $table) {
            $table->id();
            $table->addColumnRaw('uuid', 'sdm_uuid')->default(new Expression('(UUID())'))->unique();
            $table->string('sdm_no_permintaan',20)->nullable()->index();
            $table->string('sdm_no_absen',10)->unique();
            $table->date('sdm_tgl_gabung')->nullable();
            $table->string('sdm_warganegara',40)->nullable();
            $table->string('sdm_no_ktp',20)->nullable()->index();
            $table->string('sdm_nama',80)->index();
            $table->string('sdm_tempat_lahir',40)->nullable();
            $table->date('sdm_tgl_lahir')->nullable();
            $table->string('sdm_kelamin',2)->nullable();
            $table->string('sdm_gol_darah',2)->nullable();
            $table->string('sdm_alamat',120)->nullable();
            $table->unsignedTinyInteger('sdm_alamat_rt')->nullable()->default(0);
            $table->unsignedTinyInteger('sdm_alamat_rw')->nullable()->default(0);
            $table->string('sdm_alamat_kelurahan',40)->nullable();
            $table->string('sdm_alamat_kecamatan',40)->nullable();
            $table->string('sdm_alamat_kota',40)->nullable();
            $table->string('sdm_alamat_provinsi',40)->nullable();
            $table->string('sdm_alamat_kodepos',10)->nullable();
            $table->string('sdm_agama',20)->nullable();
            $table->string('sdm_no_kk',20)->nullable();
            $table->string('sdm_status_kawin',10)->nullable();
            $table->unsignedTinyInteger('sdm_jml_anak')->nullable()->default(0);
            $table->string('sdm_pendidikan',20)->nullable();
            $table->string('sdm_jurusan',60)->nullable();
            $table->string('sdm_telepon',40)->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('sdm_disabilitas',30)->nullable();
            $table->string('sdm_no_bpjs',30)->nullable()->index();
            $table->string('sdm_no_jamsostek',30)->nullable()->index();
            $table->string('sdm_no_npwp',30)->nullable();
            $table->string('sdm_nama_bank',20)->nullable();
            $table->string('sdm_cabang_bank',50)->nullable();
            $table->string('sdm_rek_bank',40)->nullable()->index();
            $table->string('sdm_an_rek',80)->nullable();
            $table->string('sdm_nama_dok',50)->nullable();
            $table->string('sdm_nomor_dok',40)->nullable();
            $table->string('sdm_penerbit_dok',60)->nullable();
            $table->string('sdm_an_dok',80)->nullable();
            $table->date('sdm_kadaluarsa_dok')->nullable();
            $table->string('sdm_uk_seragam',10)->nullable();
            $table->unsignedTinyInteger('sdm_uk_sepatu')->nullable();
            $table->text('sdm_ket_kary')->nullable();
            $table->date('sdm_tgl_berhenti')->nullable();
            $table->string('sdm_jenis_berhenti',30)->nullable();
            $table->string('sdm_ket_berhenti')->nullable();
            $table->string('sdm_id_atasan',10)->nullable()->index();
            $table->text('sdm_hak_akses')->nullable()->default('SDM-PENGGUNA')->index();
            $table->text('sdm_ijin_akses')->nullable()->index();
            $table->string('password')->nullable()->default('$2y$10$dio3jhs0CTIuctOB8SHSyO2IS4qYaufz1pLtW76XdHFgm6SktBR6C');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->string('sdm_id_pengunggah',10)->nullable();
            $table->timestamp('sdm_diunggah')->nullable();
            $table->string('sdm_id_pembuat',10)->nullable();
            $table->timestamp('sdm_dibuat')->nullable()->useCurrent();
            $table->string('sdm_id_pengubah',10)->nullable();
            $table->timestamp('sdm_diubah')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sdms');
        //DB::unprepared('DROP TYPE uuid;');
    }
};
