<?php

namespace App\Models;

use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageBoardChat extends Model
{
    use HasFactory, UUIDTrait, SoftDeletes;

    public $table = 'message_board_chats';
    public $fillable = ['user_id', 'text'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
