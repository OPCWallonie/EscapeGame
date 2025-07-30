<?php
// app/Models/Player.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    protected $fillable = ['team_id', 'name', 'device_id', 'role', 'is_active', 'last_activity', 'permissions'];
    
    protected $casts = [
        'is_active' => 'boolean',
        'last_activity' => 'datetime',
        'permissions' => 'array',
    ];
    
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
    
    public function gameEvents(): HasMany
    {
        return $this->hasMany(GameEvent::class);
    }
}