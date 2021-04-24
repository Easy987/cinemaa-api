<?php

namespace App\Models\ChatRoom;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatRoomMessageReaction extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'chat_rooms_messages_reactions';
    public $fillable = ['message_id', 'user_id', 'emoji_name'];
}
