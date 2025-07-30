<?php
// app/Models/Room.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    protected $fillable = ['order', 'slug', 'name', 'description', 'qr_code', 'type', 'parent_room_id', 'digit_reward', 'mini_game_config', 'estimated_time'];
    
    protected $casts = [
        'mini_game_config' => 'array',
    ];
    
    public function progressions(): HasMany
    {
        return $this->hasMany(Progression::class);
    }
    
    public function parentRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'parent_room_id');
    }
    
    public function branches(): HasMany
    {
        return $this->hasMany(Room::class, 'parent_room_id');
    }
}