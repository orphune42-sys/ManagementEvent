<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id('certificate_id');

            $table->unsignedBigInteger('registration_id')->unique();
            $table->foreign('registration_id')
                ->references('registration_id')
                ->on('registrations')
                ->restrictOnDelete();

            $table->string('certificate_number')->unique();
            $table->dateTime('issued_at')->nullable();
            $table->string('file_path')->nullable();
            $table->string('status')->default('Aktif');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
