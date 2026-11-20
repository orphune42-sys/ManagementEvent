<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id('registration_id');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
            $table->unsignedBigInteger('id_event');
            $table->foreign('id_event')->references('id_event')->on('events')->restrictOnDelete();
            $table->string('registration_code', 50)->unique();
            $table->dateTime('registration_date');
            $table->enum('registration_status', ['Menunggu', 'Terdaftar', 'Dibatalkan'])->default('Menunggu');
            $table->enum('payment_status', ['Belum dibayar', 'Berhasil', 'Gratis'])->default('Belum dibayar');
            $table->decimal('total_payment', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'id_event'], 'unique_user_event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
