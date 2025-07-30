<?php
// database/migrations/2024_01_01_000005_create_game_events_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('game_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('player_id')->nullable()->constrained()->onDelete('set null');
            $table->string('event_type'); // qr_scanned, puzzle_solved, digit_found, etc.
            $table->json('event_data')->nullable(); // Données spécifiques à l'événement
            $table->timestamp('occurred_at');
            $table->timestamps();
            
            $table->index(['team_id', 'occurred_at']); // Pour les requêtes de timeline
        });
    }

    public function down()
    {
        Schema::dropIfExists('game_events');
    }
};