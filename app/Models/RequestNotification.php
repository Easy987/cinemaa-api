<?php

namespace App\Models;

use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestNotification extends Model
{
    use HasFactory, UUIDTrait;

    public $fillable = ['user_id', 'seen'];
}
