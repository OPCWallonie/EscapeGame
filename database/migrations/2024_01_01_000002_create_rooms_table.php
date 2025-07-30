<?php
// database/migrations/2024_01_01_000002_create_rooms_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->integer('order')->unique(); // Ordre de progression
            $table->string('slug')->unique(); // Identifiant URL-friendly
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('qr_code')->unique(); // Code QR unique pour cette salle
            $table->enum('type', ['main', 'branch'])->default('main'); // main ou embranchement
            $table->integer('parent_room_id')->nullable(); // Pour les embranchements
            $table->integer('digit_reward')->nullable(); // Chiffre du code (1-4)
            $table->json('mini_game_config')->nullable(); // Configuration du mini-jeu
            $table->integer('estimated_time')->default(300); // Temps estimé en secondes
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rooms');
    }
};