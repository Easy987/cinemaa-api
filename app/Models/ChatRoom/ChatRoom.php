<?php

namespace App\Models\ChatRoom;

use App\Models\User;
use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatRoom extends Model
{
    use HasFactory, UUIDTrait, SoftDeletes;

    public $table = 'chat_rooms';
    public $fillable = ['name', 'user_id'];

    public function users()
    {
        return $this->hasManyThrough(User::class, ChatRoomUser::class, 'room_id', 'id', 'id', 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatRoomMessage::class, 'room_id');
    }

    public function lastMessage()
    {
        return $this->messages()->orderByDesc('created_at')->take(1)->first();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
