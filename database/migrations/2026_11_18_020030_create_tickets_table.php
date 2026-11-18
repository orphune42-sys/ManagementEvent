<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id('id_tiket');

            $table->foreignId('id_event')
                  ->constrained('events', 'id_event')
                  ->cascadeOnDelete();

            $table->string('nama_tiket');
            $table->decimal('harga', 12, 2);
            $table->integer('kuota_tiket');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
