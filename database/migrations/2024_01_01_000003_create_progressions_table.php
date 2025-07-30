<?php
// database/migrations/2024_01_01_000003_create_progressions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('progressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['entered', 'in_progress', 'completed'])->default('entered');
            $table->timestamp('entered_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('time_spent')->nullable(); // Temps passé en secondes
            $table->json('game_data')->nullable(); // Données spécifiques au mini-jeu
            $table->boolean('digit_found')->default(false); // Si le chiffre a été trouvé
            $table->integer('penalties')->default(0); // Pénalités dans cette salle
            $table->timestamps();
            
            $table->unique(['team_id', 'room_id']); // Une équipe ne peut être qu'une fois dans une salle
        });
    }

    public function down()
    {
        Schema::dropIfExists('progressions');
    }
};