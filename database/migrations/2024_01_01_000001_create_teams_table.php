<?php
// database/migrations/2024_01_01_000001_create_teams_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // Code d'accès pour rejoindre l'équipe
            $table->enum('status', ['waiting', 'playing', 'finished'])->default('waiting');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->integer('total_time')->nullable(); // Temps total en secondes
            $table->integer('penalties')->default(0); // Nombre de pénalités
            $table->boolean('is_master')->default(false); // Si c'est le téléphone maître
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('teams');
    }
};