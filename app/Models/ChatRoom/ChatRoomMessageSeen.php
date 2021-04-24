<?php

namespace App\Models\ChatRoom;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatRoomMessageSeen extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'chat_rooms_messages_seens';
    public $fillable = ['user_id', 'message_id'];

    public function message()
    {
        return $this->belongsTo(ChatRoomMessage::class, 'message_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
