<?php

namespace App\Models\ChatRoom;

use App\Models\User;
use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatRoomMessage extends Model
{
    use HasFactory, UUIDTrait, SoftDeletes;

    public $table = 'chat_rooms_messages';
    public $fillable = ['id', 'user_id', 'room_id', 'message', 'message_id'];

    public function room()
    {
        return $this->belongsTo(ChatRoom::class, 'room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function seens()
    {
        return $this->hasManyThrough(User::class, ChatRoomMessageSeen::class, 'message_id', 'id', 'id', 'user_id');
    }

    public function reactions()
    {
        return $this->hasMany(ChatRoomMessageReaction::class, 'message_id');
    }

    public function replyMessage()
    {
        return $this->belongsTo(__CLASS__, 'message_id');
    }

    public function seenByUser($userID)
    {
        return ChatRoomMessageSeen::where('message_id', $this->id)->where('user_id', $userID)->exists();
    }
}
