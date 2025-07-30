<?php
// app/Models/Progression.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Progression extends Model
{
    protected $fillable = ['team_id', 'room_id', 'status', 'entered_at', 'completed_at', 'time_spent', 'game_data', 'digit_found', 'penalties'];
    
    protected $casts = [
        'entered_at' => 'datetime',
        'completed_at' => 'datetime',
        'game_data' => 'array',
        'digit_found' => 'boolean',
    ];
    
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
    
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}