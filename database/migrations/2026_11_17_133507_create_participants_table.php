
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id('participant_id');

            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('phone_number', 20);
            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('occupation', 100)->nullable();
            $table->string('organization', 150)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
