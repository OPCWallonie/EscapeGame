<?php
// database/migrations/2024_01_01_000004_create_players_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('device_id')->unique(); // Identifiant unique du device
            $table->enum('role', ['explorer', 'technician', 'observer', 'none'])->default('none');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_activity')->nullable();
            $table->json('permissions')->nullable(); // Permissions spéciales
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('players');
    }
};