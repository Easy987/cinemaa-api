<?php

namespace App\Models;

use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatRoomFile extends Model
{
    use HasFactory, UUIDTrait, SoftDeletes;

    protected $table = 'chat_rooms_files';
}
