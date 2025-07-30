<?php
// app/Models/Team.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = ['name', 'code', 'status', 'started_at', 'finished_at', 'total_time', 'penalties', 'is_master'];
    
    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'is_master' => 'boolean',
    ];
    
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }
    
    public function progressions(): HasMany
    {
        return $this->hasMany(Progression::class);
    }
    
    public function gameEvents(): HasMany
    {
        return $this->hasMany(GameEvent::class);
    }
    
    public function currentRoom()
    {
        return $this->progressions()
            ->whereIn('status', ['entered', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->first()?->room;
    }
}