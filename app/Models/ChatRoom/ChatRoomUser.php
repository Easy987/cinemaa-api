<?php

namespace App\Models\ChatRoom;

use App\Models\User;
use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatRoomUser extends Model
{
    use HasFactory, SoftDeletes;
    public $table = 'chat_rooms_users';
    public $fillable = ['room_id', 'user_id'];

    public function room()
    {
        return $this->belongsTo(ChatRoom::class, 'room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
