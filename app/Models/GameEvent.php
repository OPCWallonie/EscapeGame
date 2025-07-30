<?php
// app/Models/GameEvent.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameEvent extends Model
{
    protected $fillable = ['team_id', 'room_id', 'player_id', 'event_type', 'event_data', 'occurred_at'];
    
    protected $casts = [
        'event_data' => 'array',
        'occurred_at' => 'datetime',
    ];
    
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
    
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
    
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}